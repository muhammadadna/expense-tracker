<?php

namespace App\Services;

use App\Models\Category;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class TelegramExpenseParser
{
    /**
     * Map of common Indonesian/English keywords to default category names.
     */
    protected array $synonyms = [
        'Food' => [
            'makan', 'makanan', 'minum', 'minuman', 'kopi', 'sarapan', 'lunch',
            'dinner', 'snack', 'jajan', 'resto', 'restoran', 'cafe', 'kafe',
            'food', 'kuliner', 'bakso', 'mie', 'nasi', 'soto', 'ayam', 'pizza',
        ],
        'Transport' => [
            'transport', 'transportasi', 'bensin', 'pertamax', 'pertalite', 'solar',
            'ojol', 'grab', 'gojek', 'maxim', 'parkir', 'tol', 'tiket', 'kereta',
            'krl', 'mrt', 'bus', 'angkot', 'taksi', 'taxi', 'service', 'servis',
            'cuci motor', 'cuci mobil', 'oli',
        ],
        'Groceries' => [
            'groceries', 'belanja', 'pasar', 'indomaret', 'alfamart', 'supermarket',
            'sembako', 'sayur', 'sayuran', 'buah', 'daging', 'beras', 'telur',
            'minyak', 'bumbu', 'sabun', 'shampoo', 'deterjen', 'toko',
        ],
        'Utilities' => [
            'utilities', 'listrik', 'pln', 'air', 'pdam', 'wifi', 'internet',
            'pulsa', 'kuota', 'gas', 'lpg', 'iuran', 'bpjs', 'tagihan',
            'pajak', 'ipl', 'sampah',
        ],
        'Entertainment' => [
            'entertainment', 'hiburan', 'nonton', 'bioskop', 'cinema', 'game',
            'steam', 'netflix', 'spotify', 'youtube', 'liburan', 'wisata',
            'staycation', 'karaoke', 'hobi',
        ],
        'Health' => [
            'health', 'kesehatan', 'obat', 'dokter', 'klinik', 'apotek',
            'rumah sakit', 'rs', 'vitamin', 'suplemen', 'medis', 'periksa',
            'gigi', 'lab', 'masker',
        ],
        'Education' => [
            'education', 'pendidikan', 'buku', 'kursus', 'sekolah', 'kuliah',
            'les', 'seminar', 'webinar', 'alat tulis', 'spp', 'uang sekolah',
        ],
        'Online' => [
            'online', 'shopee', 'tokopedia', 'lazada', 'tiktok shop', 'paket',
            'ongkir', 'olshop',
        ],
    ];

    /**
     * Indonesian month names mapping.
     */
    protected array $months = [
        'jan' => 1, 'januari' => 1, 'january' => 1,
        'feb' => 2, 'februari' => 2, 'february' => 2,
        'mar' => 3, 'maret' => 3, 'march' => 3,
        'apr' => 4, 'april' => 4,
        'mei' => 5, 'may' => 5,
        'jun' => 6, 'juni' => 6, 'june' => 6,
        'jul' => 7, 'juli' => 7, 'july' => 7,
        'agu' => 8, 'agustus' => 8, 'aug' => 8, 'august' => 8,
        'sep' => 9, 'september' => 9,
        'okt' => 10, 'oktober' => 10, 'oct' => 10, 'october' => 10,
        'nov' => 11, 'november' => 11,
        'des' => 12, 'desember' => 12, 'dec' => 12, 'december' => 12,
    ];

    /**
     * Parse text into structured transaction data.
     *
     * @return array{
     *     amount: float|null,
     *     category: \App\Models\Category|null,
     *     date: string,
     *     is_custom_date: bool,
     *     note: string|null,
     *     raw_category_hint: string|null
     * }
     */
    public function parse(string $text, ?Collection $availableCategories = null): array
    {
        $categories = $availableCategories ?? Category::all();
        $cleanText = trim($text);

        // 1. Extract Date
        [$date, $isCustomDate, $cleanText] = $this->extractDate($cleanText);

        // 2. Extract Amount
        [$amount, $cleanText] = $this->extractAmount($cleanText);

        // 3. Extract Category
        [$category, $categoryHint, $cleanText] = $this->extractCategory($cleanText, $categories);

        // 4. Remaining text is the note
        $note = trim($cleanText, " \t\n\r\0\x0B-:,");
        if (empty($note)) {
            $note = $category ? $category->name : ($categoryHint ?? 'Pengeluaran');
        }

        return [
            'amount' => $amount,
            'category' => $category,
            'date' => $date,
            'is_custom_date' => $isCustomDate,
            'note' => $note,
            'raw_category_hint' => $categoryHint,
        ];
    }

    /**
     * Extract date from the text string.
     *
     * @return array{0: string, 1: bool, 2: string} [date Y-m-d, isCustom, remainingText]
     */
    public function extractDate(string $text): array
    {
        $now = Carbon::now();
        $date = $now->toDateString();
        $isCustom = false;

        // Pattern: kemarin / yesterday
        if (preg_match('/\b(kemarin|yesterday|h-1)\b/i', $text, $matches)) {
            $date = $now->copy()->subDay()->toDateString();
            $text = trim(str_replace($matches[0], '', $text));
            return [$date, true, $text];
        }

        // Pattern: N hari lalu (e.g. 2 hari lalu)
        if (preg_match('/\b(\d+)\s+hari\s+lalu\b/i', $text, $matches)) {
            $days = (int) $matches[1];
            $date = $now->copy()->subDays($days)->toDateString();
            $text = trim(str_replace($matches[0], '', $text));
            return [$date, true, $text];
        }

        // Pattern: hari ini / today
        if (preg_match('/\b(hari\s+ini|today)\b/i', $text, $matches)) {
            $text = trim(str_replace($matches[0], '', $text));
            return [$date, false, $text];
        }

        // Pattern: @YYYY-MM-DD or YYYY-MM-DD
        if (preg_match('/@?(\d{4})[-\/](\d{1,2})[-\/](\d{1,2})/', $text, $matches)) {
            $parsed = Carbon::createFromDate((int) $matches[1], (int) $matches[2], (int) $matches[3]);
            if ($parsed) {
                $text = trim(str_replace($matches[0], '', $text));
                return [$parsed->toDateString(), true, $text];
            }
        }

        // Pattern: @DD/MM/YYYY or DD/MM/YYYY or @DD-MM-YYYY
        if (preg_match('/@?(\d{1,2})[-\/](\d{1,2})[-\/](\d{4})/', $text, $matches)) {
            $parsed = Carbon::createFromDate((int) $matches[3], (int) $matches[2], (int) $matches[1]);
            if ($parsed) {
                $text = trim(str_replace($matches[0], '', $text));
                return [$parsed->toDateString(), true, $text];
            }
        }

        // Pattern: @DD/MM or @DD-MM (current year)
        if (preg_match('/@(\d{1,2})[-\/](\d{1,2})/', $text, $matches)) {
            $parsed = Carbon::createFromDate($now->year, (int) $matches[2], (int) $matches[1]);
            if ($parsed) {
                $text = trim(str_replace($matches[0], '', $text));
                return [$parsed->toDateString(), true, $text];
            }
        }

        // Pattern: tgl DD Bulan [YYYY] (e.g. tgl 5 nov, tanggal 12 oktober 2026)
        if (preg_match('/(?:tgl|tanggal)\s+(\d{1,2})(?:\s+([a-zA-Z]+))?(?:\s+(\d{4}))?/i', $text, $matches)) {
            $day = (int) $matches[1];
            $monthStr = isset($matches[2]) ? strtolower($matches[2]) : null;
            $year = isset($matches[3]) ? (int) $matches[3] : $now->year;

            $month = $now->month;
            if ($monthStr && isset($this->months[$monthStr])) {
                $month = $this->months[$monthStr];
            }

            try {
                $parsed = Carbon::createFromDate($year, $month, $day);
                if ($parsed) {
                    $text = trim(str_replace($matches[0], '', $text));
                    return [$parsed->toDateString(), true, $text];
                }
            } catch (\Throwable $e) {
                // fall through
            }
        }

        return [$date, false, $text];
    }

    /**
     * Extract nominal amount from the string.
     * Supports: 25k, 25rb, 50.000, 1.5jt, 25000
     *
     * @return array{0: float|null, 1: string} [amount, remainingText]
     */
    public function extractAmount(string $text): array
    {
        // 1. Millions: 1.5jt, 2jt, 1,5jt, 1.5m
        if (preg_match('/(?:\b|^|rp\.?\s*)(\d+(?:[.,]\d+)?)\s*(?:jt|juta|m)\b/i', $text, $matches)) {
            $val = (float) str_replace(',', '.', $matches[1]);
            $amount = $val * 1000000;
            $text = trim(preg_replace('/' . preg_quote($matches[0], '/') . '/', '', $text, 1));
            return [$amount, $text];
        }

        // 2. Thousands with suffix: 25k, 25rb, 25.5k
        if (preg_match('/(?:\b|^|rp\.?\s*)(\d+(?:[.,]\d+)?)\s*(?:k|rb|ribu)\b/i', $text, $matches)) {
            $val = (float) str_replace(',', '.', $matches[1]);
            $amount = $val * 1000;
            $text = trim(preg_replace('/' . preg_quote($matches[0], '/') . '/', '', $text, 1));
            return [$amount, $text];
        }

        // 3. Formatted thousands with dot/comma separator: 25.000, 150,000, Rp 25.000
        if (preg_match('/(?:\b|^|rp\.?\s*)(\d{1,3}(?:[.,]\d{3})+)(?:\b|$)/i', $text, $matches)) {
            $clean = preg_replace('/[.,]/', '', $matches[1]);
            $amount = (float) $clean;
            $text = trim(preg_replace('/' . preg_quote($matches[0], '/') . '/', '', $text, 1));
            return [$amount, $text];
        }

        // 4. Plain numbers: Rp 25000, 25000 (minimum 2 digits to avoid matching day numbers)
        if (preg_match('/(?:\brp\.?\s*(\d+)\b|\b(\d{2,})\b)/i', $text, $matches)) {
            $numStr = !empty($matches[1]) ? $matches[1] : $matches[2];
            $amount = (float) $numStr;
            $text = trim(preg_replace('/' . preg_quote($matches[0], '/') . '/', '', $text, 1));
            return [$amount, $text];
        }

        return [null, $text];
    }

    /**
     * Extract category by comparing tokens with category names and synonyms.
     *
     * @return array{0: \App\Models\Category|null, 1: string|null, 2: string}
     */
    public function extractCategory(string $text, Collection $categories): array
    {
        $words = preg_split('/\s+/', $text);
        if (empty($words)) {
            return [null, null, $text];
        }

        // 1. Direct name match (case-insensitive) against database categories
        foreach ($categories as $cat) {
            $catName = strtolower($cat->name);
            foreach ($words as $word) {
                $cleanWord = strtolower(trim($word, " \t\n\r\0\x0B-:,"));
                if ($cleanWord === $catName) {
                    $newText = trim(preg_replace('/\b' . preg_quote($word, '/') . '\b/i', '', $text, 1));
                    return [$cat, $cleanWord, $newText];
                }
            }
        }

        // 2. Check synonyms dictionary
        foreach ($this->synonyms as $targetCategoryName => $synonymList) {
            // Find corresponding Category model in database
            $matchedCategory = $categories->first(function ($cat) use ($targetCategoryName) {
                return strtolower($cat->name) === strtolower($targetCategoryName);
            });

            if (!$matchedCategory) {
                // If DB category has slightly different name (e.g. "Food & Beverage")
                $matchedCategory = $categories->first(function ($cat) use ($targetCategoryName) {
                    return str_contains(strtolower($cat->name), strtolower($targetCategoryName));
                });
            }

            if ($matchedCategory) {
                foreach ($words as $word) {
                    $cleanWord = strtolower(trim($word, " \t\n\r\0\x0B-:,"));
                    if (in_array($cleanWord, $synonymList, true)) {
                        $newText = trim(preg_replace('/\b' . preg_quote($word, '/') . '\b/i', '', $text, 1));
                        return [$matchedCategory, $cleanWord, $newText];
                    }
                }
            }
        }

        // 3. Fallback: check if the first word might be intended as category hint
        $firstWord = strtolower(trim($words[0], " \t\n\r\0\x0B-:,"));
        return [null, $firstWord, $text];
    }

    /**
     * Format currency to Rupiah display.
     */
    public static function formatRupiah(float $amount): string
    {
        return 'Rp ' . number_format($amount, 0, ',', '.');
    }
}

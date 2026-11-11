<?php

namespace Tests\Unit;

use App\Models\Category;
use App\Services\TelegramExpenseParser;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use PHPUnit\Framework\TestCase;

class TelegramExpenseParserTest extends TestCase
{
    protected TelegramExpenseParser $parser;
    protected Collection $categories;

    protected function setUp(): void
    {
        parent::setUp();
        $this->parser = new TelegramExpenseParser();

        // Create sample categories in memory
        $this->categories = collect([
            new Category(['id' => 1, 'name' => 'Food', 'icon' => 'cake']),
            new Category(['id' => 2, 'name' => 'Transport', 'icon' => 'truck']),
            new Category(['id' => 3, 'name' => 'Groceries', 'icon' => 'shopping-cart']),
            new Category(['id' => 4, 'name' => 'Utilities', 'icon' => 'bolt']),
            new Category(['id' => 5, 'name' => 'Health', 'icon' => 'heart']),
        ]);
    }

    public function test_it_parses_amount_with_k_suffix(): void
    {
        $result = $this->parser->parse('makan 25k soto ayam', $this->categories);

        $this->assertEquals(25000, $result['amount']);
        $this->assertEquals('Food', $result['category']->name);
        $this->assertEquals('soto ayam', $result['note']);
    }

    public function test_it_parses_amount_with_rb_suffix(): void
    {
        $result = $this->parser->parse('50rb bensin pertamax', $this->categories);

        $this->assertEquals(50000, $result['amount']);
        $this->assertEquals('Transport', $result['category']->name);
        $this->assertEquals('pertamax', $result['note']);
    }

    public function test_it_parses_amount_with_juta_suffix(): void
    {
        $result = $this->parser->parse('listrik 1.5jt pln rumah', $this->categories);

        $this->assertEquals(1500000, $result['amount']);
        $this->assertEquals('Utilities', $result['category']->name);
        $this->assertEquals('pln rumah', $result['note']);
    }

    public function test_it_parses_amount_with_dot_thousands_separator(): void
    {
        $result = $this->parser->parse('belanja 125.000 pasar pagi', $this->categories);

        $this->assertEquals(125000, $result['amount']);
        $this->assertEquals('Groceries', $result['category']->name);
        $this->assertEquals('pasar pagi', $result['note']);
    }

    public function test_it_parses_yesterday_relative_date(): void
    {
        $yesterday = Carbon::now()->subDay()->toDateString();
        $result = $this->parser->parse('kemarin makan 30k bakso', $this->categories);

        $this->assertEquals(30000, $result['amount']);
        $this->assertEquals($yesterday, $result['date']);
        $this->assertTrue($result['is_custom_date']);
    }

    public function test_it_parses_specific_date_with_at_sign(): void
    {
        $result = $this->parser->parse('makan 45k steak @2026-11-05', $this->categories);

        $this->assertEquals(45000, $result['amount']);
        $this->assertEquals('2026-11-05', $result['date']);
        $this->assertTrue($result['is_custom_date']);
        $this->assertEquals('steak', $result['note']);
    }

    public function test_it_parses_date_in_text_format(): void
    {
        $now = Carbon::now();
        $result = $this->parser->parse('tgl 5 nov obat 50k apotek', $this->categories);

        $this->assertEquals(50000, $result['amount']);
        $this->assertEquals('Health', $result['category']->name);
        $this->assertEquals('2026-11-05', $result['date']);
        $this->assertTrue($result['is_custom_date']);
    }

    public function test_it_returns_null_category_when_ambiguous(): void
    {
        $result = $this->parser->parse('45000 bayar sesuatu', $this->categories);

        $this->assertEquals(45000, $result['amount']);
        $this->assertNull($result['category']);
    }
}

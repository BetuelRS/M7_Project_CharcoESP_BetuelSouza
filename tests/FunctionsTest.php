<?php
require_once __DIR__ . '/../includes/functions.php';

use PHPUnit\Framework\TestCase;

class FunctionsTest extends TestCase
{
    public function testTipoParaUnidade()
    {
        $this->assertEquals('°C', tipo_para_unidade('Temperatura'));
        $this->assertEquals('%', tipo_para_unidade('Humidade'));
        $this->assertEquals('lux', tipo_para_unidade('Luminosidade'));
        $this->assertEquals('µg/m3', tipo_para_unidade('Qualidade do Ar'));
        $this->assertEquals('cm', tipo_para_unidade('Nível da Água'));
        $this->assertEquals('', tipo_para_unidade('Desconhecido'));
    }

    public function testTipoParaIcone()
    {
        $this->assertEquals('fa-thermometer-half', tipo_para_icone('Temperatura'));
        $this->assertEquals('fa-tint', tipo_para_icone('Humidade'));
        $this->assertEquals('fa-chart-line', tipo_para_icone('Inexistente'));
    }

    public function testTiposOrdenados()
    {
        $tipos = tipos_ordenados();
        $this->assertCount(5, $tipos);
        $this->assertEquals('Temperatura', $tipos[0]);
        $this->assertEquals('Nível da Água', $tipos[4]);
    }

    public function testValidarValorUnidade()
    {
        // Temperatura
        $this->assertEmpty(validar_valor_unidade(25, '°C'));
        $this->assertNotEmpty(validar_valor_unidade(200, '°C'));

        // Percentagem
        $this->assertEmpty(validar_valor_unidade(50, '%'));
        $this->assertNotEmpty(validar_valor_unidade(150, '%'));

        // Lux
        $this->assertEmpty(validar_valor_unidade(500, 'lux'));
        $this->assertNotEmpty(validar_valor_unidade(200000, 'lux'));

        // µg/m3
        $this->assertEmpty(validar_valor_unidade(25, 'µg/m3'));
        $this->assertNotEmpty(validar_valor_unidade(600, 'µg/m3'));

        // cm / m
        $this->assertEmpty(validar_valor_unidade(10, 'cm'));
        $this->assertNotEmpty(validar_valor_unidade(-5, 'cm'));

        // Unidade desconhecida (sem validação)
        $this->assertEmpty(validar_valor_unidade(999, 'V'));

        // String não numérica
        $this->assertNotEmpty(validar_valor_unidade('abc', '%'));
    }
}

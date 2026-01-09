<?php

namespace App\Helpers;

class PhoneHelper
{
    /**
     * Formata o telefone para o padrão do banco: +1XXXXXXXXXX
     * Remove todos os caracteres não numéricos e adiciona o código do país
     *
     * @param string|null $phone
     * @param string $countryCode
     * @return string|null
     */
    public static function formatPhoneForDatabase(?string $phone, string $countryCode = '+1'): ?string
    {
        if (empty($phone)) {
            return null;
        }

        // Remove todos os caracteres não numéricos exceto o +
        $cleaned = preg_replace('/[^\d+]/', '', $phone);

        // Remove o + se existir no início
        $cleaned = ltrim($cleaned, '+');

        // Se já começar com código do país (ex: 1 para US) e tiver 11 dígitos, formata
        if (strlen($cleaned) == 11 && strpos($cleaned, '1') === 0) {
            return '+' . $cleaned;
        }

        // Se tiver exatamente 10 dígitos, adiciona código do país
        if (strlen($cleaned) == 10) {
            // Remove o código do país do parâmetro se tiver +
            $code = str_replace('+', '', $countryCode);
            return '+' . $code . $cleaned;
        }

        // Se já começar com + e tiver formato válido, retorna como está
        if (strpos($phone, '+') === 0 && preg_match('/^\+1\d{10}$/', $phone)) {
            return $phone;
        }

        // Se não tiver 10 ou 11 dígitos válidos, retorna null
        return null;
    }

    /**
     * Valida se o telefone está no formato correto: +1XXXXXXXXXX ou pode ser formatado
     *
     * @param string|null $phone
     * @return bool
     */
    public static function validatePhoneFormat(?string $phone): bool
    {
        if (empty($phone)) {
            return true; // Campo opcional
        }

        // Remove caracteres não numéricos exceto +
        $cleaned = preg_replace('/[^\d+]/', '', $phone);
        $cleaned = ltrim($cleaned, '+');

        // Verifica se tem 10 dígitos (será formatado com +1) ou 11 dígitos começando com 1
        if (strlen($cleaned) == 10) {
            return true; // Pode ser formatado como +1XXXXXXXXXX
        }

        if (strlen($cleaned) == 11 && strpos($cleaned, '1') === 0) {
            return true; // Já tem código do país, pode ser formatado como +1XXXXXXXXXX
        }

        // Verifica se já está no formato correto +1XXXXXXXXXX
        if (preg_match('/^\+1\d{10}$/', $phone) === 1) {
            return true;
        }

        return false;
    }

    /**
     * Formata o telefone do banco para exibição: XXX-XXX-XXXX
     *
     * @param string|null $phone
     * @return string|null
     */
    public static function formatPhoneForDisplay(?string $phone): ?string
    {
        if (empty($phone)) {
            return null;
        }

        // Remove o código do país (+1) e formata
        $cleaned = preg_replace('/[^\d]/', '', $phone);
        
        // Remove o código do país (1) se estiver presente
        if (strlen($cleaned) == 11 && strpos($cleaned, '1') === 0) {
            $cleaned = substr($cleaned, 1);
        }

        // Se tiver 10 dígitos, formata XXX-XXX-XXXX
        if (strlen($cleaned) == 10) {
            return substr($cleaned, 0, 3) . '-' . substr($cleaned, 3, 3) . '-' . substr($cleaned, 6, 4);
        }

        return $phone; // Retorna como está se não conseguir formatar
    }

    /**
     * Extrai apenas os dígitos do telefone
     *
     * @param string|null $phone
     * @return string
     */
    public static function extractDigits(?string $phone): string
    {
        if (empty($phone)) {
            return '';
        }
        return preg_replace('/[^\d]/', '', $phone);
    }
}


<?php

if (!function_exists('dollars_to_cents')) {
    /**
     * Converte dólares para centavos
     *
     * @param float|int|string $dollars
     * @return int
     */
    function dollars_to_cents($dollars): int
    {
        if ($dollars === null) {
            return 0;
        }
        
        return (int) round((float) $dollars * 100);
    }
}

if (!function_exists('cents_to_dollars')) {
    /**
     * Converte centavos para dólares
     *
     * @param int|string $cents
     * @return float
     */
    function cents_to_dollars($cents): float
    {
        if ($cents === null || $cents === 0) {
            return 0.0;
        }
        
        return round((int) $cents / 100, 2);
    }
}

if (!function_exists('format_money')) {
    /**
     * Formata valor monetário (aceita dólares ou centavos)
     *
     * @param float|int $amount
     * @param bool $isCents Se true, o valor está em centavos
     * @return string
     */
    function format_money($amount, bool $isCents = false): string
    {
        if ($amount === null) {
            return '$0.00';
        }
        
        $dollars = $isCents ? cents_to_dollars($amount) : (float) $amount;
        
        return '$' . number_format($dollars, 2, '.', ',');
    }
}


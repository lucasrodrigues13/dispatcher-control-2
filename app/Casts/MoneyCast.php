<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

/**
 * Money Cast
 * 
 * Converte valores monetários entre centavos (integer no banco) e dólares (float no código)
 * 
 * Uso no Model:
 * protected $casts = [
 *     'price' => MoneyCast::class,
 * ];
 * 
 * No código, trabalhe com dólares (float):
 * $model->price = 10.50; // Armazena 1050 (centavos) no banco
 * echo $model->price; // Retorna 10.5 (dólares)
 */
class MoneyCast implements CastsAttributes
{
    /**
     * Transforma o valor do banco de dados (centavos) para o valor do atributo (dólares)
     *
     * @param  Model  $model
     * @param  string  $key
     * @param  mixed  $value
     * @param  array  $attributes
     * @return float|null
     */
    public function get($model, string $key, $value, array $attributes)
    {
        if ($value === null) {
            return null;
        }

        // Converte centavos (integer) para dólares (float)
        return round($value / 100, 2);
    }

    /**
     * Transforma o valor do atributo (dólares) para o valor do banco de dados (centavos)
     *
     * @param  Model  $model
     * @param  string  $key
     * @param  mixed  $value
     * @param  array  $attributes
     * @return int|null
     */
    public function set($model, string $key, $value, array $attributes)
    {
        if ($value === null) {
            return null;
        }

        // Converte dólares (float) para centavos (integer)
        return (int) round($value * 100);
    }
}

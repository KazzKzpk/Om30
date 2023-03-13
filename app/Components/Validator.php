<?php

namespace App\Components;

class Validator
{
    public static function validateCPF(string $value)
    {
        $cpf = preg_replace( '/[^0-9]/is', '', $value);
        if (strlen($cpf) !== 11)
            return false;
        if (preg_match('/(\d)\1{10}/', $cpf))
            return false;

        for ($t = 9; $t < 11; $t++) {
            for ($d = 0, $c = 0; $c < $t; $c++)
                $d += $cpf[$c] * (($t + 1) - $c);
            $d = ((10 * $d) % 11) % 10;
            if ($cpf[$c] != $d)
                return false;
        }
        return true;
    }

    public static function validateCNS(string $value)
    {
        $cns = preg_replace( '/[^0-9]/is', '', $value);
        if (strlen($cns) !== 15)
            return false;
        if (preg_match('/(\d)\1{10}/', $cns))
            return false;

        if ($cns[0] === '1' || $cns[0] === '2') {
            $pis = substr($cns, 0, 11);
            $s = 0;
            for ($i = 0; $i < 11; $i++)
                $s += ($cns[$i] * (15 - $i));
            $dv = (11 - ($s % 11));
            $result = ($pis . '00');

            if ($dv === 11)
                $dv = 0;
            if ($dv === 10) {
                $dv = (11 - (($s + 2) % 11));
                $result .= '1';
            } else $result .= '0';

            $result .= $dv;
            return ($result === $cns);
        }
        elseif ($cns[0] === '7' || $cns[0] === '8' || $cns[0] === '9') {
            $s = 0;
            for ($i = 0; $i < 15; $i++)
                $s += ($cns[$i] * (15 - $i));
            return (($s % 11) === 0);
        }

        return false;
    }
}

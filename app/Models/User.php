<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class User extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'name_matchcode',
        'name_mother',
        'birth',
        'cpf',
        'cns'
    ];

    public function address()
    { return $this->hasOne(UserAddress::class); }

    public function updateMatchCode()
    { $this->name_matchcode = Str::matchCode($this->name); }

    public static function find(int $id)
    { return self::with('address')->where('id', $id)->get(); }

    public static function getPaginated(int $count = 10)
    { return self::with('address')->latest()->paginate($count); }

    public static function getByCPF(string $cpf)
    { return self::with('address')->where('cpf', $cpf)->get(); }

    public static function getByCNS(string $cns)
    { return self::with('address')->where('cns', $cns)->get(); }

    public static function isRegisteredCPF(string $cpf)
    { return (count(self::getByCPF($cpf)->toArray()) > 0); }

    public static function isRegisteredCNS(string $cns)
    { return (count(self::getByCNS($cns)->toArray()) > 0); }

    public static function getByMatchCode(string $matchCode)
    { return self::with('address')->where('name_matchcode', 'like', '%' . Str::matchCode($matchCode) . '%')->get(); }
}

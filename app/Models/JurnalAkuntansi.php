<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JurnalAkuntansi extends Model
{
    use HasFactory;

    protected $table = 'jurnal_akuntansis';

    protected $guarded = ['id'];
}

<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Support\Facades\DB;

trait UsesCaseInsensitiveSearch
{
    /**
     * Operator pencarian yang aman untuk database lokal maupun Supabase/PostgreSQL.
     *
     * PostgreSQL memakai ILIKE agar pencarian tidak case-sensitive.
     * MySQL/SQLite tetap memakai LIKE agar kompatibel dengan collation default.
     */
    protected function caseInsensitiveLikeOperator(): string
    {
        return DB::connection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';
    }

    /**
     * Bentuk keyword untuk query LIKE/ILIKE.
     */
    protected function likeSearchTerm(string $search): string
    {
        return '%' . trim($search) . '%';
    }
}

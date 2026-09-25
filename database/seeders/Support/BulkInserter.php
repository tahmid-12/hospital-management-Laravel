<?php

namespace Database\Seeders\Support;

use Illuminate\Support\Facades\DB;

/**
 * Buffers plain arrays and writes them as multi-row INSERT statements:
 *
 *   INSERT INTO t (a, b) VALUES (?, ?), (?, ?), ... -- ~1,000 rows per round trip
 *
 * instead of one INSERT (and one network round trip + statement parse) per row.
 * Memory stays flat because the buffer is emptied after every chunk.
 */
final class BulkInserter
{
    // PostgreSQL allows at most 65,535 bind parameters per statement.
    private const MAX_PARAMETERS = 65000;

    private array $rows = [];

    private ?int $chunkSize = null;

    public int $inserted = 0;

    public function __construct(
        private readonly string $table,
        private readonly int $preferredChunkSize,
    ) {}

    public function add(array $row): void
    {
        $this->chunkSize ??= max(1, min($this->preferredChunkSize, intdiv(self::MAX_PARAMETERS, count($row))));
        $this->rows[] = $row;

        if (count($this->rows) >= $this->chunkSize) {
            $this->flush();
        }
    }

    public function flush(): int
    {
        if ($this->rows !== []) {
            DB::table($this->table)->insert($this->rows);
            $this->inserted += count($this->rows);
            $this->rows = [];
        }

        return $this->inserted;
    }
}

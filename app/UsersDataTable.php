<?php

namespace App\DataTables;

use Yajra\DataTables\Services\DataTable;

class UsersDataTable extends DataTable
{
    public function html()
    {
        return $this->builder()
            ->columns($this->getColumns())
            ->parameters([
                'dom' => 'Bfrtip',
                'buttons' => ['excel', 'csv', 'pdf', 'print'],
            ]);
    }

    // Other methods...
}

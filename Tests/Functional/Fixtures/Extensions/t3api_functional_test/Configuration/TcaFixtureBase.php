<?php

/**
 * Shared skeleton for the fixture tables' TCA: every table uses the same ctrl section
 * (title label, soft delete, enable columns) and a single type — only the human-readable
 * title, the columns and the showitem list differ per table.
 */
return static function (string $title, array $columns, string $showitem, string $labelField = 'title'): array {
    return [
        'ctrl' => [
            'title' => $title,
            'label' => $labelField,
            'delete' => 'deleted',
            'enablecolumns' => [
                'disabled' => 'hidden',
                'starttime' => 'starttime',
                'endtime' => 'endtime',
            ],
        ],
        'columns' => $columns,
        'types' => [
            '0' => ['showitem' => $showitem],
        ],
    ];
};

<?php

namespace App\Filament\Clusters\ActivityTasks\Resources\Tasks\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class TaskForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('lead_id')
                    ->relationship('lead', 'company_name'),
                Select::make('customer_id')
                    ->relationship('customer', 'name'),
                Select::make('contact_id')
                    ->relationship('contact', 'name'),
                Select::make('opportunity_id')
                    ->relationship('opportunity', 'name'),
                TextInput::make('title')
                    ->required(),
                Textarea::make('description')
                    ->columnSpanFull(),
                DateTimePicker::make('start_at'),
                DateTimePicker::make('due_at'),
                Select::make('assignee_id')
                    ->relationship('assignee', 'name')
                    ->default(fn (): ?int => auth()->id())
                    ->required(),
                Select::make('status')
                    ->options([
                        'not_started' => '未开始',
                        'in_progress' => '进行中',
                        'completed' => '已完成',
                        'ignored' => '已忽略',
                        'cancelled' => '已取消',
                    ])
                    ->required()
                    ->default('not_started'),
                Select::make('priority')
                    ->options([
                        'low' => '低',
                        'normal' => '普通',
                        'high' => '高',
                        'urgent' => '紧急',
                    ])
                    ->required()
                    ->default('normal'),
            ]);
    }
}

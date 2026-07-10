<?php

namespace Kirschbaum\Commentions\Filament\Actions;

use Filament\Actions\Action;

/**
 * Compatibility base class for {@see CommentsTableAction}.
 *
 * Filament 4 unified actions under `Filament\Actions\Action`, removing the
 * dedicated `Filament\Tables\Actions\Action` table-action base used by Filament 3.
 *
 * This class extends the unified base, which is correct for Filament 4 and 5. On
 * Filament 3, CommentionsServiceProvider::aliasTableAction() aliases this class
 * name to the legacy `Filament\Tables\Actions\Action` before it is autoloaded, so
 * the file below is only ever loaded on Filament 4/5.
 */
class TableAction extends Action {}

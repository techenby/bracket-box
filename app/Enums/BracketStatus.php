<?php

namespace App\Enums;

enum BracketStatus: string
{
    case Draft = 'draft';
    case Active = 'active';
    case Completed = 'completed';
}

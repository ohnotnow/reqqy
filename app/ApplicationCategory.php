<?php

namespace App;

enum ApplicationCategory: string
{
    case Internal = 'internal';
    case External = 'external';
    case Proposed = 'proposed';
}

<?php

namespace App;

enum DocumentType: string
{
    case Prd = 'prd';
    case TechnicalAssessment = 'technical_assessment';
    case Research = 'research';
}

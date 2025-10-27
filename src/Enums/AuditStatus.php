<?php

namespace Dock\A11yChecker\Enums;

enum AuditStatus: string
{
    case TO_TESTS = 'to-tests';
    case FAILED = 'failed';
    case PASSED = 'passed';
}
<?php

namespace Dock\A11yChecker\Enums;

enum Sort: string
{
    case CREATED_AT_ASC = 'created_at_asc';
    case CREATED_AT_DESC = 'created_at_desc';
    case LAST_AUDIT_ASC = 'last_audit_asc';
    case LAST_AUDIT_DESC = 'last_audit_desc';
}
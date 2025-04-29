<?php

namespace Eclipse\Common\Enums;

enum JobStatus: string
{
    case QUEUED = 'queued';
    case COMPLETED = 'completed';
    case FAILED = 'failed';
}

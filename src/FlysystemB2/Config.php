<?php

namespace iansltx\FlysystemB2;

interface Config
{
    const FAIL_WRITE_IF_EXISTS = 1 << 0;
    const ALLOW_PSEUDO_DIRECTORY_DELETE = 1 << 1;
    const ALLOW_LOCAL_RENAME = 1 << 2;
    const ALLOW_LOCAL_COPY = 1 << 3;
    const HIDE_ON_DELETE = 1 << 4;
}
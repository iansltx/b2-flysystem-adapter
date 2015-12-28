<?php

namespace iansltx\B2Client;

interface ServerSettingsInterface
{
    public function getAccountId();
    public function getToken();
    public function getBaseAPIURL();
    public function getBaseDownloadURL();
}

<?php

use App\Mcp\ContentMcpRegistration;
use App\Mcp\Servers\ContentOperationsServer;
use Laravel\Mcp\Facades\Mcp;

if (ContentMcpRegistration::shouldRegister()) {
    Mcp::web(ContentMcpRegistration::path(), ContentOperationsServer::class)
        ->middleware(ContentMcpRegistration::middleware());
}

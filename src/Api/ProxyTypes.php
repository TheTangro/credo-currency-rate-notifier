<?php

namespace App\Api;

enum ProxyTypes
{
    case SOCKS5;
    case SOCKS4;
    case HTTPS;
}

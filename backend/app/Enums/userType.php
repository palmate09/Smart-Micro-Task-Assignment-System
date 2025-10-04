<?php

namespace App\Enums;

enum userType : string {
    case admin = 'admin'; 
    case company = 'company'; 
    case worker = 'worker'; 
}

enum availability_status: string {
    case available = 'available'; 
    case busy = 'busy'; 
    case offline = 'offline'; 
}

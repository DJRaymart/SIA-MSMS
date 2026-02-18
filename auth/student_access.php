<?php

require_once __DIR__ . '/admin_helper.php';
require_once __DIR__ . '/student_helper.php';

function isStudentAccessible($module, $page = null) {
    if (isAdminLoggedIn()) {
        return true; 
    }

    if (!isStudentLoggedIn()) {
        return false;
    }

    if ($page === null) {
        $page = basename($_SERVER['PHP_SELF']);
        $path = $_SERVER['REQUEST_URI'];
    }

    $studentPages = [
        'sciLab' => [
            'log_book.php',           
            'lab_booking.php',        
        ],
        'avr' => [
            'reservation.php',
            'avr_logbook.php',
        ],
        'queue' => [
            'display.php',            
            'portal.php',             
        ],
        'ictOffice' => [
            'logbook.php',            
        ],
        'library' => [
            'log_book.php',
            'lib_logbok.php',
        ],
    ];

    if (isset($studentPages[$module])) {
        foreach ($studentPages[$module] as $allowedPage) {
            if (strpos($page, $allowedPage) !== false || strpos($path, $allowedPage) !== false) {
                return true;
            }
        }
    }
    
    return false;
}

function getStudentSidebarItems($module) {
    $items = [];
    
    switch ($module) {
        case 'sciLab':
            $items = [
                ['url' => '/sciLab/logs/log_book.php', 'file' => 'log_book.php', 'label' => 'Log Attendance', 'icon' => '<svg class="w-5 h-5 text-blue-600 group-hover:text-blue-700" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>'],
                ['url' => '/sciLab/reservation/lab_booking.php', 'file' => 'lab_booking.php', 'label' => 'Book Laboratory', 'icon' => '<svg class="w-5 h-5 text-blue-600 group-hover:text-blue-700" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" /></svg>'],
            ];
            break;
            
        case 'avr':
            $items = [
                ['url' => '/avr/avr_logbook.php', 'file' => 'avr_logbook.php', 'label' => 'Log Attendance', 'icon' => '<svg class="w-5 h-5 text-blue-600 group-hover:text-blue-700" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>'],
                ['url' => '/avr/modules/reservation.php', 'file' => 'reservation.php', 'label' => 'Reservation', 'icon' => '<svg class="w-5 h-5 text-blue-600 group-hover:text-blue-700" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" /></svg>'],
            ];
            break;
            
        case 'queue':
            $items = [
                ['url' => '/queue/portal.php', 'file' => 'portal.php', 'label' => 'Get Queue', 'icon' => '<svg class="w-5 h-5 text-blue-600 group-hover:text-blue-700" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>'],
                ['url' => '/queue/display.php', 'file' => 'display.php', 'label' => 'Queue Display', 'icon' => '<svg class="w-5 h-5 text-blue-600 group-hover:text-blue-700" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>'],
            ];
            break;
            
        case 'ictOffice':
            $items = [
                ['url' => '/ictOffice/public/?page=logbook', 'file' => 'logbook.php', 'label' => 'Log Book', 'icon' => '<svg class="w-5 h-5 text-blue-600 group-hover:text-blue-700" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>'],
            ];
            break;
            
        case 'library':
            $items = [
                ['url' => '/library/lib_logbok.php', 'file' => 'lib_logbok.php', 'label' => 'Log Book', 'icon' => '<svg class="w-5 h-5 text-blue-600 group-hover:text-blue-700" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>'],
            ];
            break;
    }
    
    return $items;
}

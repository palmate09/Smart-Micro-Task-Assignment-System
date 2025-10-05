<?php

$data = [
            'name' => 'required|string|max:256', 
            'email' => 'required|string|email|unique:users, email', 
            'password' => 'required|string|min:6',
            'role' => 'required|string|in:admin, user, worker', 
            'rating' => 'nullable|numeric|min:0|max:5', 
            'availability_status' => 'required|string|in:offline, busy, available'
        ];

    $ans = array_keys($data);
    
    foreach($data as $a => $b){
        echo $a . "\n"; 
    }
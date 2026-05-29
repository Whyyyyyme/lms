<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;

class ChatbotController extends Controller
{
    public function index()
    {
        return view('student.chatbot.index');
    }
}

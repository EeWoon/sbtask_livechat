<?php

namespace App\Http\Controllers;

use App\Message;
use App\User;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Pusher\Pusher;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        //select all users except logged in user and count unread message 

        $users = User::select(['id','name','email'])->where('id', '!=', Auth::id())->withCount(['fromMessage as unread'=>function($q){
            $q->where('to',Auth::id())->where('is_read',0);
        }])->get();

        return view('home', ['users' => $users]);
    }

    public function getMessage($user_id) 
    {
        $my_id = Auth::id();

        //read all unread msg
        Message::where(['from' => $user_id, 'to' => $my_id])->update(['is_read' => 1]);

        //get all msg from selected user
        $messages = Message::where(function ($query) use ($user_id, $my_id) {
            $query->where('from', $user_id)->where('to', $my_id);
        })->oRwhere(function ($query) use ($user_id, $my_id) {
            $query->where('from', $my_id)->where('to', $user_id);
        })->get();

        return view('messages.index', ['messages' => $messages]);
    }

    public function sendMessage(Request $request)
    {
        $from = Auth::id();
        $to = $request->receiver_id;
        $message = $request->message;

        $data = new Message();
        $data->from = $from;
        $data->to = $to;
        $data->message = $message;
        $data->is_read = 0; //msg will be unread
        $data->save();

        //pusher
        $options = array(
            'cluster' => 'ap1',
            'useTLS' => true
        );

        $pusher = new Pusher(
            env('PUSHER_APP_KEY'),
            env('PUSHER_APP_SECRET'),
            env('PUSHER_APP_ID'),
            $options
        );

        $data = ['from' => $from, 'to' => $to,'event'=>'new-message']; // sending from and to user id when pressed enter
        $pusher->trigger('my-channel-'.$to, 'my-event', $data);
    }

    public function sendTyping(Request $request)
    {
        $from = Auth::id();
        $to = $request->receiver_id;

        //pusher
        $options = array(
            'cluster' => 'ap1',
            'useTLS' => true
        );

        $pusher = new Pusher(
            env('PUSHER_APP_KEY'),
            env('PUSHER_APP_SECRET'),
            env('PUSHER_APP_ID'),
            $options
        );

        $data = ['from' => $from, 'to' => $to,'event'=>'user-typing']; // sending from and to user id when typing
        $pusher->trigger('my-channel-'.$to, 'my-event', $data);
        
    }
}

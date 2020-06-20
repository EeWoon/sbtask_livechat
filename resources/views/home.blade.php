@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-4">
            <div class="user-wrapper">
                <ul class="users">
                    @foreach($users as $user)
                        <li class="user" id="{{ $user->id }}">
                            @if($user->unread)
                                <span class="pending"> {{ $user->unread }} </span>
                            @endif
                                    
                            <div class="media-body"> 
                                <span class="d-inline-block float-right invisible text-muted" id="typingElement_{{$user->id}}"> Typing... </span>
                                <span class="d-inline-block">
                                    <p class="name"> {{ $user->name }} </p>
                                    <p class="email>"> {{ $user->email }} </p>
                                </span>
                            </div>

                        </li>
                    @endforeach
                </ul>
            </div>
        </div>

        <div class="col-md-8" id="messages">
            
        </div>

    </div>
</div>
@endsection

@extends('layouts.default')

@section('title', 'メール認証誘導画面')

@section('css')
<link rel="stylesheet" href="{{ asset('/css/verify.css') }}">
@endsection

@section('content')
@include('components.header')
<div class="mail_notice--div">
    <div class="mail_notice--header">
        <p class="notice_header--p">登録していただいたメールアドレスに認証メールを送付しました。</p>
    </div>

    <div class="mail_notice--content">
        <div class="alert_resend">
            メール認証を完了してください。
            <form class="mail_resend--form" method="POST" action="{{route('verification.send') }}">
                @csrf
                <p>もし、認証用のメールを受け取っていない場合以下のボタンをクリックして認証用のメールを受け取ってください。</p>
                <button type="submit" class="mail_resend--button">認証メールを再送する</button>
                @if (session('resent'))
                    <p class="notice_resend--p" role="alert">
                        新規認証メールを再送信しました！
                    </p>
                @endif
            </form>
        </div>
    </div>
</div>
@endsection

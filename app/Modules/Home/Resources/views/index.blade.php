@extends('layouts.app', ['title' => __('messages.home.title')])

@section('content')
    <section class="dashboard">
        <div class="hero home-hero">
            <div class="home-hero-copy">
                <p class="home-eyebrow">{{ __('messages.home.eyebrow') }}</p>
                <h1>{{ __('messages.home.title') }}</h1>
                <p class="home-description">{{ __('messages.home.description') }}</p>
            </div>

            <div class="home-hero-actions">
                <a href="{{ route('consent.index') }}" class="action-btn">{{ __('messages.home.primary_action') }}</a>
                <p class="home-language-hint">{{ __('messages.home.language_hint') }}</p>
            </div>
        </div>
    </section>
@endsection

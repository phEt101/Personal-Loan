@extends('layouts.app', ['title' => $delegation->exists ? __('workdelegation::messages.edit') : __('workdelegation::messages.add')])

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/work-delegation.css') }}?v={{ filemtime(public_path('css/work-delegation.css')) }}">
@endpush

@section('content')
    @php
        $defaultGroupId = $groups->count() === 1 ? $groups->first()->id : null;
        $selectedGroupId = old('responsibility_group_id', $delegation->responsibility_group_id ?? $defaultGroupId);
    @endphp
    <section class="dashboard module-page delegation-page">
        <div class="hero compact-hero">
            <div class="hero-body">
                <h2>{{ $delegation->exists ? __('workdelegation::messages.edit') : __('workdelegation::messages.add') }}</h2>
                <p>{{ __('workdelegation::messages.form_subtitle') }}</p>
            </div>
        </div>

        <div class="module-page-actions delegation-back-action">
            <a href="{{ route('work-delegations.index') }}" class="action-btn outline">← {{ __('workdelegation::messages.back') }}</a>
        </div>

        <div class="module-card delegation-form-card">
            <form method="POST" action="{{ $delegation->exists ? route('work-delegations.update', $delegation) : route('work-delegations.store') }}">
                @csrf
                @if ($delegation->exists) @method('PUT') @endif

                <div class="delegation-section">
                    <div class="delegation-section-heading">
                        <span class="delegation-section-number">1</span>
                        <div>
                            <h3>{{ __('workdelegation::messages.assignment_section') }}</h3>
                            <p>{{ __('workdelegation::messages.assignment_section_hint') }}</p>
                        </div>
                    </div>

                    @unless ($isAdmin)
                        <div class="delegation-current-user">
                            <span>{{ __('workdelegation::messages.delegator') }}</span>
                            <strong>{{ $currentUser->full_name }}</strong>
                            <small>{{ $currentUser->employee_code }}</small>
                        </div>
                    @endunless

                    <div class="delegation-form-grid">
                        @if ($isAdmin)
                        <div class="form-group">
                            <label for="delegator_user_id">{{ __('workdelegation::messages.delegator') }} <span class="required-asterisk">*</span></label>
                            <select id="delegator_user_id" name="delegator_user_id" required>
                                <option value="">{{ __('workdelegation::messages.select_delegator') }}</option>
                                @foreach ($delegators as $user)
                                    <option value="{{ $user->id }}" @selected((int) old('delegator_user_id', $delegation->delegator_user_id) === $user->id)>
                                        {{ $user->employee_code }} — {{ $user->full_name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('delegator_user_id') <small class="field-error">{{ $message }}</small> @enderror
                        </div>
                        @endif

                        <div class="form-group">
                        <label for="responsibility_group_id">{{ __('workdelegation::messages.group') }} <span class="required-asterisk">*</span></label>
                        <select id="responsibility_group_id" name="responsibility_group_id" required>
                            <option value="">{{ __('workdelegation::messages.select_group') }}</option>
                            @foreach ($groups as $group)
                                <option value="{{ $group->id }}" @selected((int) $selectedGroupId === $group->id)>{{ $group->name }}</option>
                            @endforeach
                        </select>
                        @error('responsibility_group_id') <small class="field-error">{{ $message }}</small> @enderror
                        </div>

                        <div class="form-group">
                        <label for="delegate_user_id">{{ __('workdelegation::messages.delegate') }} <span class="required-asterisk">*</span></label>
                        <select id="delegate_user_id" name="delegate_user_id" required>
                            <option value="">{{ __('workdelegation::messages.select_delegate') }}</option>
                            @foreach ($delegates as $user)
                                <option value="{{ $user->id }}" @selected((int) old('delegate_user_id', $delegation->delegate_user_id) === $user->id)>
                                    {{ $user->employee_code }} — {{ $user->full_name }}
                                </option>
                            @endforeach
                        </select>
                        @error('delegate_user_id') <small class="field-error">{{ $message }}</small> @enderror
                        </div>
                    </div>
                </div>

                <div class="delegation-section">
                    <div class="delegation-section-heading">
                        <span class="delegation-section-number">2</span>
                        <div>
                            <h3>{{ __('workdelegation::messages.period_section') }}</h3>
                            <p>{{ __('workdelegation::messages.status_hint') }}</p>
                        </div>
                    </div>
                    <div class="delegation-form-grid delegation-period-grid">
                        <div class="form-group">
                        <label for="starts_at">{{ __('workdelegation::messages.starts_at') }} <span class="required-asterisk">*</span></label>
                        <input id="starts_at" name="starts_at" type="datetime-local" required value="{{ old('starts_at', $delegation->starts_at?->timezone(config('app.local_timezone'))->format('Y-m-d\TH:i') ?? now(config('app.local_timezone'))->format('Y-m-d\TH:i')) }}">
                        @error('starts_at') <small class="field-error">{{ $message }}</small> @enderror
                        </div>

                        <div class="form-group">
                        <label for="ends_at">{{ __('workdelegation::messages.ends_at') }} <span class="required-asterisk">*</span></label>
                        <input id="ends_at" name="ends_at" type="datetime-local" required value="{{ old('ends_at', $delegation->ends_at?->timezone(config('app.local_timezone'))->format('Y-m-d\TH:i') ?? now(config('app.local_timezone'))->addDay()->format('Y-m-d\TH:i')) }}">
                        @error('ends_at') <small class="field-error">{{ $message }}</small> @enderror
                        </div>
                    </div>
                </div>

                <div class="delegation-section delegation-section--note">
                    <div class="form-group delegation-note-field">
                        <label for="delegation_note">{{ __('workdelegation::messages.note') }}</label>
                        <textarea id="delegation_note" name="note" rows="3" maxlength="1000">{{ old('note', $delegation->note) }}</textarea>
                        @error('note') <small class="field-error">{{ $message }}</small> @enderror
                    </div>
                </div>

                <div class="form-actions delegation-form-actions">
                    <a href="{{ route('work-delegations.index') }}" class="action-btn outline">{{ __('workdelegation::messages.back') }}</a>
                    <button type="submit" class="action-btn">{{ __('workdelegation::messages.save') }}</button>
                </div>
            </form>
        </div>
    </section>
@endsection

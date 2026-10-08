@extends('layouts.app', ['title' => __('settings::messages.responsibility_groups')])

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/settings.css') }}?v={{ filemtime(public_path('css/settings.css')) }}">
@endpush

@section('content')
    <section class="dashboard module-page">
        <div class="hero compact-hero">
            <div class="hero-body">
                <h2>{{ $group->exists ? __('settings::messages.edit_responsibility_group') : __('settings::messages.add_responsibility_group') }}</h2>
                <p>{{ __('settings::messages.responsibility_group_form_subtitle') }}</p>
            </div>
        </div>

        <div class="settings-create-back responsibility-back-link">
            <a href="{{ route('settings.responsibility-groups.index') }}" class="action-btn outline">← {{ __('settings::messages.responsibility_groups') }}</a>
        </div>

        <div class="module-card">
            <form method="POST" action="{{ $group->exists ? route('settings.responsibility-groups.update', $group) : route('settings.responsibility-groups.store') }}" class="module-form">
                @csrf
                @if ($group->exists) @method('PUT') @endif

                <div class="responsibility-group-basics">
                    <div class="module-field">
                        <label for="responsibility_group_name">{{ __('settings::messages.group_name') }} <span class="required-asterisk">*</span></label>
                        <input id="responsibility_group_name" name="name" value="{{ old('name', $group->name) }}" required maxlength="100">
                        @error('name')<small class="field-error">{{ $message }}</small>@enderror
                    </div>

                    <div class="module-field">
                        <label for="responsibility_group_status">{{ __('settings::messages.status') }}</label>
                        <select id="responsibility_group_status" name="is_active">
                            <option value="1" @selected((string) old('is_active', (int) $group->is_active) === '1')>{{ __('settings::messages.active') }}</option>
                            <option value="0" @selected((string) old('is_active', (int) $group->is_active) === '0')>{{ __('settings::messages.inactive') }}</option>
                        </select>
                    </div>
                </div>

                @php
                    $selectedInternals = collect(old('internal_user_ids', $group->exists ? $group->internalUsers->modelKeys() : []))->map(fn ($id) => (int) $id);
                    $selectedExternals = collect(old('external_user_ids', $group->exists ? $group->externalUsers->modelKeys() : []))->map(fn ($id) => (int) $id);
                @endphp

                <div class="responsibility-member-panels">
                    <section class="responsibility-member-panel" data-member-panel>
                        <div class="responsibility-member-panel-heading">
                            <div>
                                <h4>{{ __('settings::messages.internal_members') }}</h4>
                                <p>{{ __('settings::messages.internal_members_hint') }}</p>
                            </div>
                            <span data-selected-count></span>
                        </div>
                        <input class="responsibility-member-search" type="search" placeholder="{{ __('settings::messages.search_members') }}" data-member-search>
                        <div class="responsibility-member-list">
                            @foreach ($internalUsers as $user)
                                <label class="responsibility-member-option" data-member-option data-search-text="{{ mb_strtolower($user->employee_code.' '.$user->full_name) }}">
                                    <input type="checkbox" name="internal_user_ids[]" value="{{ $user->id }}" @checked($selectedInternals->contains($user->id))>
                                    <span class="responsibility-member-copy">
                                        <strong>{{ $user->full_name }}</strong>
                                        <small>{{ $user->employee_code }}</small>
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    </section>

                    <section class="responsibility-member-panel" data-member-panel>
                        <div class="responsibility-member-panel-heading">
                            <div>
                                <h4>{{ __('settings::messages.external_members') }}</h4>
                                <p>{{ __('settings::messages.external_members_hint') }}</p>
                            </div>
                            <span data-selected-count></span>
                        </div>
                        <input class="responsibility-member-search" type="search" placeholder="{{ __('settings::messages.search_members') }}" data-member-search>
                        <div class="responsibility-member-list">
                            @foreach ($externalUsers as $user)
                                @php $assignedGroupId = $externalAssignments[$user->id] ?? null; @endphp
                                <label class="responsibility-member-option" data-member-option data-search-text="{{ mb_strtolower($user->employee_code.' '.$user->full_name) }}">
                                    <input type="checkbox" name="external_user_ids[]" value="{{ $user->id }}" @checked($selectedExternals->contains($user->id))>
                                    <span class="responsibility-member-copy">
                                        <strong>{{ $user->full_name }}</strong>
                                        <small>{{ $user->employee_code }}</small>
                                        @if ($assignedGroupId && (int) $assignedGroupId !== (int) $group->id)
                                            <em>{{ __('settings::messages.assigned_to_another_group') }}</em>
                                        @endif
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    </section>
                </div>

                <div class="module-form-actions">
                    <button type="submit" class="action-btn">{{ __('settings::messages.save_changes') }}</button>
                </div>
            </form>
        </div>
    </section>

    <script>
        document.querySelectorAll('[data-member-panel]').forEach((panel) => {
            const options = Array.from(panel.querySelectorAll('[data-member-option]'));
            const counter = panel.querySelector('[data-selected-count]');
            const search = panel.querySelector('[data-member-search]');
            const updateCount = () => {
                const count = options.filter((option) => option.querySelector('input').checked).length;
                counter.textContent = @js(__('settings::messages.selected_members')).replace(':count', count);
            };

            search.addEventListener('input', () => {
                const query = search.value.trim().toLocaleLowerCase();
                options.forEach((option) => {
                    option.hidden = query !== '' && !option.dataset.searchText.includes(query);
                });
            });
            options.forEach((option) => option.querySelector('input').addEventListener('change', updateCount));
            updateCount();
        });
    </script>
@endsection

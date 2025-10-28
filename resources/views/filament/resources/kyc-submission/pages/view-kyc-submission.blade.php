<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Section 1: Submission Information --}}
        <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <div class="fi-section-header flex items-center gap-x-3 overflow-hidden px-6 py-4">
                <div class="grid flex-1 gap-y-1">
                    <h3 class="fi-section-header-heading text-base font-semibold leading-6 text-gray-950 dark:text-white">
                        Submission Information
                    </h3>
                </div>
            </div>
            <div class="fi-section-content-ctn border-t border-gray-200 dark:border-white/10">
                <div class="fi-section-content p-6">
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <dt class="text-sm font-medium text-gray-700 dark:text-gray-300">Reference Number</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white font-bold">#{{ $record->id }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-700 dark:text-gray-300">Form Type</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $record->form?->name ?? 'N/A' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-700 dark:text-gray-300">Submitted At</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $record->created_at->format('M d, Y H:i A') }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-700 dark:text-gray-300">Current Status</dt>
                            <dd class="mt-1">
                                <span class="fi-badge fi-badge-{{ match($record->status) {
                                    'pending' => 'gray',
                                    'under_review' => 'info',
                                    'verified' => 'warning',
                                    'approved' => 'success',
                                    'declined' => 'danger',
                                    default => 'gray'
                                } }} inline-flex items-center gap-x-1.5 rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset">
                                    {{ ucwords(str_replace('_', ' ', $record->status)) }}
                                </span>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-700 dark:text-gray-300">Verification Status</dt>
                            <dd class="mt-1">
                                <span class="fi-badge fi-badge-{{ match($record->verification_status) {
                                    'not_verified' => 'gray',
                                    'verified' => 'success',
                                    'failed' => 'danger',
                                    default => 'gray'
                                } }} inline-flex items-center gap-x-1.5 rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset">
                                    {{ ucwords(str_replace('_', ' ', $record->verification_status)) }}
                                </span>
                            </dd>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Section 2: Applicant Details --}}
        <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <div class="fi-section-header flex items-center gap-x-3 overflow-hidden px-6 py-4">
                <div class="grid flex-1 gap-y-1">
                    <h3 class="fi-section-header-heading text-base font-semibold leading-6 text-gray-950 dark:text-white">
                        Applicant Details
                    </h3>
                </div>
            </div>
            <div class="fi-section-content-ctn border-t border-gray-200 dark:border-white/10">
                <div class="fi-section-content p-6">
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        @foreach($record->submission_data ?? [] as $key => $value)
                            @if(!empty($value))
                                <div>
                                    <dt class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                        {{ ucwords(str_replace('_', ' ', $key)) }}
                                    </dt>
                                    <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                                        @if(is_array($value))
                                            @if(isset($value['path']) || isset($value['url']))
                                                @php
                                                    $path = $value['path'] ?? '';
                                                    $url = Storage::url($path);
                                                    $filename = basename($path);
                                                @endphp
                                                <a href="{{ $url }}" target="_blank" class="text-primary-600 hover:underline flex items-center gap-1">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                                    </svg>
                                                    {{ $filename }}
                                                </a>
                                            @else
                                                <pre class="text-xs">{{ json_encode($value, JSON_PRETTY_PRINT) }}</pre>
                                            @endif
                                        @elseif($this->isDate($key, $value))
                                            @php
                                                try {
                                                    $formatted = \Carbon\Carbon::parse($value)->format('M d, Y');
                                                } catch (\Exception $e) {
                                                    $formatted = $value;
                                                }
                                            @endphp
                                            {{ $formatted }}
                                        @elseif($this->isPhone($key))
                                            {{ $this->formatPhone($value) }}
                                        @elseif($this->isEmail($key, $value))
                                            <a href="mailto:{{ $value }}" class="text-primary-600 hover:underline">{{ $value }}</a>
                                        @else
                                            {{ $value }}
                                        @endif
                                    </dd>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- Section 3: Verification Details --}}
        @if($record->verification_status !== \App\Models\KycSubmission::VERIFICATION_NOT_VERIFIED || $record->verificationLogs->isNotEmpty())
            <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                <div class="fi-section-header flex items-center gap-x-3 overflow-hidden px-6 py-4">
                    <div class="grid flex-1 gap-y-1">
                        <h3 class="fi-section-header-heading text-base font-semibold leading-6 text-gray-950 dark:text-white">
                            Verification Details
                        </h3>
                    </div>
                </div>
                <div class="fi-section-content-ctn border-t border-gray-200 dark:border-white/10">
                    <div class="fi-section-content p-6">
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <dt class="text-sm font-medium text-gray-700 dark:text-gray-300">Verification Provider</dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $record->verificationLogs->first()?->verification_provider ?? 'YouVerify' }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-700 dark:text-gray-300">Verification Date</dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $record->verificationLogs->first()?->created_at?->format('M d, Y H:i A') ?? 'N/A' }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-700 dark:text-gray-300">Verification Status</dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $record->verificationLogs->first()?->status ? ucwords($record->verificationLogs->first()->status) : 'N/A' }}</dd>
                            </div>
                        </div>
                        @if(!empty($record->verification_response))
                            <div class="mt-4">
                                <dt class="text-sm font-medium text-gray-700 dark:text-gray-300">Verification Response</dt>
                                <dd class="mt-1">
                                    <pre class="text-xs text-gray-900 dark:text-white bg-gray-50 dark:bg-gray-800 p-3 rounded">{{ json_encode($record->verification_response, JSON_PRETTY_PRINT) }}</pre>
                                </dd>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endif

        {{-- Section 4: Review Information --}}
        @if($record->reviewed_by !== null || $record->decline_reason !== null)
            <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                <div class="fi-section-header flex items-center gap-x-3 overflow-hidden px-6 py-4">
                    <div class="grid flex-1 gap-y-1">
                        <h3 class="fi-section-header-heading text-base font-semibold leading-6 text-gray-950 dark:text-white">
                            Review Information
                        </h3>
                    </div>
                </div>
                <div class="fi-section-content-ctn border-t border-gray-200 dark:border-white/10">
                    <div class="fi-section-content p-6">
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <dt class="text-sm font-medium text-gray-700 dark:text-gray-300">Reviewed By</dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $record->reviewer?->name ?? 'Not reviewed yet' }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-700 dark:text-gray-300">Reviewed At</dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $record->reviewed_at?->format('M d, Y H:i A') ?? 'N/A' }}</dd>
                            </div>
                        </div>
                        @if(!empty($record->decline_reason))
                            <div class="mt-4">
                                <dt class="text-sm font-medium text-gray-700 dark:text-gray-300">Decline Reason</dt>
                                <dd class="mt-1">
                                    <div class="rounded-md bg-red-50 dark:bg-red-900/20 p-4">
                                        <p class="text-sm text-red-800 dark:text-red-200">{{ $record->decline_reason }}</p>
                                    </div>
                                </dd>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endif
    </div>
</x-filament-panels::page>

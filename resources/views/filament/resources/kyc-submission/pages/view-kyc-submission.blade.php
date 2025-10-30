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
                                    'approved' => 'success',
                                    'declined' => 'danger',
                                    'disapproved' => 'danger',
                                    default => 'gray'
                                } }} inline-flex items-center gap-x-1.5 rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset">
                                    {{ ucwords(str_replace('_', ' ', $record->status)) }}
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
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        @foreach($record->submission_data ?? [] as $key => $value)
                            @if(!empty($value))
                                <div>
                                    @if(is_array($value))
                                        @if(isset($value['path']) || isset($value['url']))
                                            {{-- File/Image field - display separately --}}
                                            <div class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                                {{ ucwords(str_replace('_', ' ', $key)) }}
                                            </div>
                                            @php
                                                $path = $value['path'] ?? '';
                                                $url = Storage::url($path);
                                                $filename = basename($path);
                                                $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
                                                $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                                            @endphp
                                            @if(in_array($extension, $imageExtensions))
                                                <div class="space-y-2">
                                                    <a href="{{ $url }}" target="_blank" class="block">
                                                        <img src="{{ $url }}" alt="{{ $filename }}" class="rounded-lg border border-gray-200 dark:border-gray-700 max-w-xs max-h-64 object-contain hover:opacity-90 transition-opacity">
                                                    </a>
                                                    <a href="{{ $url }}" target="_blank" class="text-primary-600 hover:underline inline-flex items-center gap-0.5 text-xs">
                                                        <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                        </svg>
                                                        View full size
                                                    </a>
                                                </div>
                                            @else
                                                <a href="{{ $url }}" target="_blank" class="text-primary-600 hover:underline inline-flex items-center gap-0.5">
                                                    <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                                    </svg>
                                                    {{ $filename }}
                                                </a>
                                            @endif
                                        @else
                                            {{-- Array field - display inline --}}
                                            <span class="text-sm">
                                                <span class="font-medium text-gray-700 dark:text-gray-300">{{ ucwords(str_replace('_', ' ', $key)) }}:</span>
                                                <span class="text-gray-900 dark:text-white">
                                                    <pre class="inline text-xs">{{ json_encode($value, JSON_PRETTY_PRINT) }}</pre>
                                                </span>
                                            </span>
                                        @endif
                                    @elseif(is_string($value) && str_starts_with($value, 'data:image'))
                                        {{-- Display base64 encoded images --}}
                                        <div class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            {{ ucwords(str_replace('_', ' ', $key)) }}
                                        </div>
                                        <div class="space-y-2">
                                            <img src="{{ $value }}" alt="{{ ucwords(str_replace('_', ' ', $key)) }}" class="rounded-lg border border-gray-200 dark:border-gray-700 max-w-xs max-h-64 object-contain">
                                            <a href="{{ $value }}" download="{{ $key }}.png" class="text-primary-600 hover:underline text-xs">
                                                Download image
                                            </a>
                                        </div>
                                    @else
                                        {{-- Regular field - display inline --}}
                                        <span class="text-sm">
                                            <span class="font-medium text-gray-700 dark:text-gray-300">{{ ucwords(str_replace('_', ' ', $key)) }}:</span>
                                            <span class="text-gray-900 dark:text-white">
                                                @if($this->isDate($key, $value))
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
                                                @elseif(is_bool($value) || $value === '1' || $value === '0')
                                                    {{ $value == '1' || $value === true ? 'true' : ($value === '0' || $value === false ? 'false' : $value) }}
                                                @else
                                                    {{ $value }}
                                                @endif
                                            </span>
                                        </span>
                                    @endif
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- Section 3: Review Information --}}
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

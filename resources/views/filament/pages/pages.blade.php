<x-filament-panels::page>
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
        <h1 class="text-3xl font-bold mb-6 text-gray-800 dark:text-white">
            صفحات کتاب: {{ $this->book->title }}
        </h1>
        
        @if($this->book->pages && $this->book->pages->count() > 0)
            <div class="space-y-4">
                @foreach($this->book->pages as $page)
                    <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 hover:shadow-md transition-shadow">
                        <div class="flex justify-between items-start">
                            <div class="flex-1">
                                <h3 class="text-xl font-semibold text-gray-900 dark:text-white">
                                    صفحه {{ $page->page_number ?? $loop->iteration }}
                                </h3>
                                <p class="text-gray-600 dark:text-gray-400 mt-2">
                                    {{ $page->content ?? 'بدون محتوا' }}
                                </p>
                            </div>
                            <div class="flex gap-2">
                                {{-- <x-filament::button 
                                    size="sm" 
                                    color="primary"
                                    tag="a"
                                    href="{{ route('book.pages.edit', [$this->book, $page]) }}">
                                    ویرایش
                                </x-filament::button> --}}
                                
                                <x-filament::icon-button 
                                    icon="heroicon-o-trash" 
                                    size="sm"
                                    color="danger"
                                    tooltip="حذف"
                                    wire:click="deletePage({{ $page->id }})"
                                    wire:confirm="آیا مطمئن هستید؟" />
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-gray-500 dark:text-gray-400 text-center py-8">
                هنوز صفحه‌ای برای این کتاب ثبت نشده است.
            </p>
        @endif
        
        <div class="mt-6">
            {{-- <x-filament::button 
                color="gray"
                tag="a"
                href="{{ route('filament.admin.pages.book') }}">
                بازگشت به لیست کتاب‌ها
            </x-filament::button> --}}
        </div>
    </div>
</x-filament-panels::page>
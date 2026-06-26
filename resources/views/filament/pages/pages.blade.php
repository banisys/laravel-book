<x-filament-panels::page>

  <div class="grid grid-cols-6 gap-4">

    {{-- کل صفحات --}}
    <div
      class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-4 flex items-center gap-3">
      <div class="rounded-full bg-blue-100 dark:bg-blue-400/10 p-2.5">
        <x-heroicon-o-book-open class="w-5 h-5 text-blue-600 dark:text-blue-400" />
      </div>
      <div>
        <p class="text-xs text-gray-500 dark:text-gray-400">کل صفحات</p>
        <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ $book->pages->count() }}</p>
      </div>
    </div>

    {{-- دارای متن --}}
    <div
      class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-4 flex items-center gap-3">
      <div class="rounded-full bg-emerald-100 dark:bg-emerald-400/10 p-2.5">
        <x-heroicon-o-document-check class="w-5 h-5 text-emerald-600 dark:text-emerald-400" />
      </div>
      <div>
        <p class="text-xs text-gray-500 dark:text-gray-400">دارای متن</p>
        <p class="text-2xl font-bold text-emerald-600 dark:text-emerald-400">
          {{ $book->pages->whereNotNull('content')->where('content', '!=', '')->count() }}
        </p>
      </div>
    </div>

    {{-- افزوده شده به RAG --}}
    <div
      class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-4 flex items-center gap-3">
      <div class="rounded-full bg-violet-100 dark:bg-violet-400/10 p-2.5">
        <x-heroicon-o-circle-stack class="w-5 h-5 text-violet-600 dark:text-violet-400" />
      </div>
      <div>
        <p class="text-xs text-gray-500 dark:text-gray-400">افزوده شده به RAG</p>
        <p class="text-2xl font-bold text-violet-600 dark:text-violet-400">
          {{ $book->pages->where('is_synced_to_rag', true)->count() }}
        </p>
      </div>
    </div>

    <div></div>

    {{-- خلاصه --}}
    <div
      class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-4 flex flex-col justify-between gap-2">
      <div class="flex items-center gap-2 mb-1">
        <x-heroicon-o-document-text class="w-4 h-4 text-amber-500" />
        <p class="text-xs font-medium text-gray-500 dark:text-gray-400">خلاصه</p>
      </div>
      <div class="flex flex-col gap-2">
        <x-filament::button color="warning" size="sm" icon="heroicon-o-sparkles" wire:click="openSummaryModal">
          ساخت خلاصه
        </x-filament::button>
        <x-filament::button color="gray" size="sm" icon="heroicon-o-clock" tag="a"
          href="{{ route('filament.admin.pages.books.{book}.summaries', ['book' => $book->id]) }}">
          خلاصه‌های قبلی
        </x-filament::button>
      </div>
    </div>

    {{-- آزمون --}}
    <div
      class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-4 flex flex-col justify-between gap-2">
      <div class="flex items-center gap-2 mb-1">
        <x-heroicon-o-academic-cap class="w-4 h-4 text-rose-500" />
        <p class="text-xs font-medium text-gray-500 dark:text-gray-400">آزمون</p>
      </div>
      <div class="flex flex-col gap-2">
        <x-filament::button color="danger" size="sm" icon="heroicon-o-pencil-square" wire:click="openQuizModal">
          ساخت آزمون
        </x-filament::button>
        <x-filament::button color="gray" size="sm" icon="heroicon-o-clock" tag="a"
          href="{{ route('filament.admin.pages.books.{book}.summaries', ['book' => $book->id]) }}">
          آزمون‌های قبلی
        </x-filament::button>
      </div>
    </div>

    {{-- جستجو --}}
    <div class="col-span-3 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-4">
      <div class="flex items-center gap-2 mb-2">
        <x-heroicon-o-funnel class="w-4 h-4 text-gray-500 dark:text-gray-400" />
        <p class="text-xs font-medium text-gray-500 dark:text-gray-400">فیلتر بر اساس بازه صفحه</p>
      </div>
      <div class="flex items-center gap-3">
        <x-filament::input.wrapper class="flex-1">
          <x-filament::input type="number" wire:model.live="searchFrom" placeholder="از صفحه" min="1" />
        </x-filament::input.wrapper>
        <div class="flex items-center gap-1 text-gray-400">
          <x-heroicon-o-arrows-right-left class="w-4 h-4" />
        </div>
        <x-filament::input.wrapper class="flex-1">
          <x-filament::input type="number" wire:model.live="searchTo" placeholder="تا صفحه" min="1" />
        </x-filament::input.wrapper>
      </div>
    </div>



  </div>

  <x-filament::section>
    <div class="overflow-x-auto">
      <table class="w-full text-right text-sm">
        <tbody>
          @forelse($this->getFilteredPages() as $page)
            <tr class="border-b border-gray-100 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-800/50">

              <td class="px-3 py-2 w-12 text-gray-500 dark:text-gray-400">
                {{ $page->page_number }}
              </td>

              <td class="px-3 py-2 w-32">
                <img src="{{ Storage::url($page->image_path) }}"
                  class="rounded border border-gray-200 dark:border-gray-700 w-24 h-auto cursor-pointer" x-data
                  x-on:click="$dispatch('open-modal', { id: 'page-modal-{{ $page->id }}' })" />
              </td>

              <td class="px-3 py-2">
                <textarea id="content-{{ $page->id }}" rows="6"
                  x-on:click="$dispatch('open-modal', { id: 'page-modal-{{ $page->id }}' })"
                  class="w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 p-2 text-sm resize-y">{{ $page->content }}</textarea>
              </td>

              <td class="px-3 py-2 w-40">
                <div class="flex flex-col gap-2">

                  <x-filament::button size="sm" color="primary"
                    wire:click="openProcessModal({{ $page->id }})">
                    <span>استخراج متن</span>
                  </x-filament::button>

                  @if (!$page->is_synced_to_rag)
                    @if ($page->content)
                      <x-filament::button size="sm" color="success" wire:click="addToRag({{ $page->id }})">
                        افزودن به RAG
                      </x-filament::button>
                    @endif
                  @else
                    <x-filament::button size="sm" color="danger" wire:click="deleteFromRag({{ $page->id }})">
                      حذف از RAG
                    </x-filament::button>
                  @endif

                </div>
              </td>

            </tr>

            <x-filament::modal id="page-modal-{{ $page->id }}" width="7xl">
              <div class="grid grid-cols-2 gap-4 h-[90vh] overflow-y-auto">

                <div class="flex items-center justify-center">
                  <img src="{{ Storage::url($page->image_path) }}"
                    class="max-h-[90vh] w-auto rounded border border-gray-200 dark:border-gray-700" />
                </div>

                <div class="flex flex-col gap-3">

                  <div class="rounded-lg border border-gray-200 dark:border-gray-700 p-3 flex flex-col gap-3">

                    <div>
                      <div x-data="{
                          open: false,
                          selected: [],
                          prompts: {{ $this->getReadPrompts()->map(fn($p) => ['id' => $p->id, 'title' => Str::limit($p->title, 60)])->values()->toJson() }},
                          toggle(id) {
                              if (this.selected.includes(id)) {
                                  this.selected = this.selected.filter(i => i !== id);
                              } else {
                                  this.selected.push(id);
                              }
                              $wire.set('selectedReadPrompts', this.selected);
                          },
                          isSelected(id) {
                              return this.selected.includes(id);
                          }
                      }" class="relative" x-on:click.outside="open = false">
                        {{-- trigger --}}
                        <div x-on:click="open = !open"
                          class="min-h-[38px] w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 p-2 text-sm cursor-pointer flex flex-wrap gap-1">
                          <template x-if="selected.length === 0">
                            <span class="text-gray-400"></span>
                          </template>انتخاب پرامپت

                          <template x-for="id in selected" :key="id">
                            <span
                              class="inline-flex items-center gap-1 bg-primary-100 dark:bg-primary-900 text-primary-700 dark:text-primary-300 rounded px-2 py-0.5 text-xs">
                              <span x-text="prompts.find(p => p.id === id)?.title"></span>
                              <button type="button" x-on:click.stop="toggle(id)"
                                class="hover:text-red-500">&times;</button>
                            </span>
                          </template>
                        </div>

                        <div x-show="open" x-cloak
                          class="absolute z-50 mt-1 w-full rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 shadow-lg max-h-48 overflow-y-auto">
                          <template x-if="prompts.length === 0">
                            <div class="px-3 py-2 text-sm text-gray-400">پرامپتی وجود ندارد</div>
                          </template>

                          <template x-for="prompt in prompts" :key="prompt.id">
                            <div x-on:click="toggle(prompt.id)"
                              class="flex items-center gap-2 px-3 py-2 text-sm cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-800"
                              :class="isSelected(prompt.id) ?
                                  'bg-primary-50 dark:bg-primary-900/20 text-primary-700 dark:text-primary-300' :
                                  'text-gray-700 dark:text-gray-300'">
                              <div class="w-4 h-4 rounded border flex items-center justify-center flex-shrink-0"
                                :class="isSelected(prompt.id) ? 'bg-primary-600 border-primary-600' :
                                    'border-gray-300 dark:border-gray-600'">
                                <template x-if="isSelected(prompt.id)">
                                  <svg class="w-3 h-3 text-white" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                      d="M5 13l4 4L19 7" />
                                  </svg>
                                </template>
                              </div>
                              <span x-text="prompt.title"></span>
                            </div>
                          </template>
                        </div>

                      </div>
                    </div>

                    <div>
                      <x-filament::input.wrapper>
                        <x-filament::input.select wire:model="selectedModel"
                          class="[direction:rtl] bg-[left_0.75rem_center] pr-3 pl-10">
                          <option value="gapgpt-qwen-3.5">GapGPT Qwen 3.5</option>
                          <option value="gapgpt-4o">GapGPT 4o</option>
                          <option value="gapgpt-4o-mini">GapGPT 4o Mini</option>
                        </x-filament::input.select>
                      </x-filament::input.wrapper>
                    </div>

                    <x-filament::button size="sm" color="primary" wire:click="processPage({{ $page->id }})"
                      wire:loading.attr="disabled" wire:target="processPage({{ $page->id }})">
                      <span wire:loading.remove wire:target="processPage({{ $page->id }})">استخراج متن صفحه</span>
                      <span wire:loading wire:target="processPage({{ $page->id }})">در حال پردازش...</span>
                    </x-filament::button>

                  </div>

                  <div class="flex flex-col gap-2 flex-1">
                    <textarea id="modal-content-{{ $page->id }}" rows="20"
                      class="w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 p-3 text-sm resize-y flex-1">{{ $page->content }}</textarea>

                    <x-filament::button size="sm" color="success" x-data
                      x-on:click="$wire.updateContent({{ $page->id }}, document.getElementById('modal-content-{{ $page->id }}').value)">
                      ذخیره متن
                    </x-filament::button>
                  </div>

                </div>
              </div>
            </x-filament::modal>
          @empty
            <tr>
              <td colspan="4" class="px-3 py-8 text-center text-gray-400">
                صفحه‌ای یافت نشد.
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    {{-- Pagination --}}
    @if ($this->getTotalPages() > 1)
      <div class="flex items-center justify-between mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">

        <p class="text-sm text-gray-500 dark:text-gray-400">
          صفحه {{ $currentPage }} از {{ $this->getTotalPages() }}
        </p>

        <div class="flex items-center gap-1">

          <x-filament::icon-button icon="heroicon-o-chevron-right" size="sm" color="gray"
            wire:click="previousPage" :disabled="$currentPage === 1" />

          @for ($i = 1; $i <= $this->getTotalPages(); $i++)
            <x-filament::button size="sm" :color="$currentPage === $i ? 'primary' : 'gray'" wire:click="goToPage({{ $i }})">
              {{ $i }}
            </x-filament::button>
          @endfor

          <x-filament::icon-button icon="heroicon-o-chevron-left" size="sm" color="gray"
            wire:click="nextPage" :disabled="$currentPage === $this->getTotalPages()" />

        </div>

      </div>
    @endif

  </x-filament::section>

  <x-filament::modal id="summary-modal" width="5xl">

    <div class="space-y-4">

      <div class="grid grid-cols-6 gap-4 items-end">

        <div class="col-span-3">
          <div x-data="{
              open: false,
              selected: [],
              prompts: {{ $this->getSummaryPrompts()->map(fn($p) => ['id' => $p->id, 'title' => Str::limit($p->title, 60)])->values()->toJson() }},
              toggle(id) {
                  if (this.selected.includes(id)) {
                      this.selected = this.selected.filter(i => i !== id);
                  } else {
                      this.selected.push(id);
                  }
                  $wire.set('selectedSummaryPrompts', this.selected);
              },
              isSelected(id) {
                  return this.selected.includes(id);
              }
          }" class="relative" x-on:click.outside="open = false">
            {{-- trigger --}}
            <div x-on:click="open = !open"
              class="min-h-[38px] w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 p-2 text-sm cursor-pointer flex flex-wrap gap-1">
              <template x-if="selected.length === 0">
                <span class="text-gray-400"></span>
              </template>انتخاب پرامپت

              <template x-for="id in selected" :key="id">
                <span
                  class="inline-flex items-center gap-1 bg-primary-100 dark:bg-primary-900 text-primary-700 dark:text-primary-300 rounded px-2 py-0.5 text-xs">
                  <span x-text="prompts.find(p => p.id === id)?.title"></span>
                  <button type="button" x-on:click.stop="toggle(id)" class="hover:text-red-500">&times;</button>
                </span>
              </template>
            </div>

            <div x-show="open" x-cloak
              class="absolute z-50 mt-1 w-full rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 shadow-lg max-h-48 overflow-y-auto">
              <template x-if="prompts.length === 0">
                <div class="px-3 py-2 text-sm text-gray-400">پرامپتی وجود ندارد</div>
              </template>

              <template x-for="prompt in prompts" :key="prompt.id">
                <div x-on:click="toggle(prompt.id)"
                  class="flex items-center gap-2 px-3 py-2 text-sm cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-800"
                  :class="isSelected(prompt.id) ?
                      'bg-primary-50 dark:bg-primary-900/20 text-primary-700 dark:text-primary-300' :
                      'text-gray-700 dark:text-gray-300'">
                  <div class="w-4 h-4 rounded border flex items-center justify-center flex-shrink-0"
                    :class="isSelected(prompt.id) ? 'bg-primary-600 border-primary-600' :
                        'border-gray-300 dark:border-gray-600'">
                    <template x-if="isSelected(prompt.id)">
                      <svg class="w-3 h-3 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                      </svg>
                    </template>
                  </div>
                  <span x-text="prompt.title"></span>
                </div>
              </template>
            </div>

          </div>
        </div>

        <div>
          <label class="text-sm font-medium">از صفحه</label>
          <x-filament::input.wrapper>
            <x-filament::input wire:model="fromPage" type="number" />
          </x-filament::input.wrapper>
        </div>

        <div>
          <label class="text-sm font-medium">تا صفحه</label>
          <x-filament::input.wrapper>
            <x-filament::input type="number" wire:model="toPage" min="1" />
          </x-filament::input.wrapper>
        </div>

        <div>
          <x-filament::button wire:click="getSummary" color="primary">
            دریافت خلاصه
          </x-filament::button>

        </div>
      </div>

      <textarea rows="20" wire:model="summaryResult" class="w-full rounded-lg border p-3">
        </textarea>

    </div>

    <x-slot name="footerActions">

      <x-filament::button color="success" wire:click="storeSummary">
        ذخیره
      </x-filament::button>

      <x-filament::button color="gray" x-on:click="$dispatch('close-modal', { id: 'summary-modal' })">
        بستن
      </x-filament::button>

    </x-slot>

  </x-filament::modal>

  @push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" />
    <style>
      .choices__inner {
        background-color: transparent !important;
        border-color: rgb(209 213 219) !important;
        border-radius: 0.5rem !important;
        font-size: 0.875rem !important;
      }

      .dark .choices__inner {
        border-color: rgb(55 65 81) !important;
        color: white !important;
      }

      .choices__list--dropdown {
        border-color: rgb(209 213 219) !important;
        border-radius: 0.5rem !important;
        font-size: 0.875rem !important;
      }

      .dark .choices__list--dropdown {
        background-color: rgb(17 24 39) !important;
        border-color: rgb(55 65 81) !important;
        color: white !important;
      }

      .dark .choices__list--dropdown .choices__item--selectable {
        color: rgb(209 213 219) !important;
      }

      .dark .choices__list--dropdown .choices__item--selectable.is-highlighted {
        background-color: rgb(55 65 81) !important;
      }

      .choices__item--selectable.is-selected {
        background-color: rgb(59 130 246) !important;
        color: white !important;
      }

      .dark .choices__input {
        background-color: transparent !important;
        color: white !important;
      }
    </style>
  @endpush

  @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>
  @endpush
</x-filament-panels::page>

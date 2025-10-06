       <!-- Static sidebar for desktop -->
       <div class="hidden xl:fixed xl:inset-y-0 xl:z-50 xl:flex xl:w-72 xl:flex-col">
           <!-- Sidebar component, swap this element with another sidebar if you like -->
           <div
               class="flex grow flex-col gap-y-5 overflow-y-auto bg-gradient-to-b from-gray-900 to-gray-950 px-6 ring-1 ring-white/10 shadow-2xl">
               <div class="flex h-16 shrink-0 items-center border-b border-white/10">
                   <div class="flex items-center gap-3">
                       <div
                           class="w-8 h-8 rounded-lg bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center">
                           <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                               <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                   d="M13 10V3L4 14h7v7l9-11h-7z" />
                           </svg>
                       </div>
                       <span class="font-bold text-white text-xl">{{ env('APP_NAME') }}</span>
                   </div>
               </div>
               <nav class="flex flex-1 flex-col">
                   <ul role="list" class="flex flex-1 flex-col gap-y-7">
                       <li>
                           <ul role="list" class="-mx-2 space-y-2">
                               {{-- Core Navigation --}}
                               <li>
                                   <a href="/dashboard"
                                       class="@if (request()->is('dashboard')) sidebar-item-active @else sidebar-item @endif">
                                       <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2"
                                           stroke="currentColor" aria-hidden="true">
                                           <path stroke-linecap="round" stroke-linejoin="round"
                                               d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" />
                                           <polyline points="9 22 9 12 15 12 15 22" />
                                       </svg>
                                       <span>Dashboard</span>
                                   </a>
                               </li>
                               <li>
                                   <a href="/servers"
                                       class="@if (request()->is('servers*')) sidebar-item-active @else sidebar-item @endif">
                                       <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2"
                                           stroke="currentColor" aria-hidden="true">
                                           <path stroke-linecap="round" stroke-linejoin="round"
                                               d="M21.75 17.25v-.228a4.5 4.5 0 00-.12-1.03l-2.268-9.64a3.375 3.375 0 00-3.285-2.602H7.923a3.375 3.375 0 00-3.285 2.602l-2.268 9.64a4.5 4.5 0 00-.12 1.03v.228m19.5 0a3 3 0 01-3 3H5.25a3 3 0 01-3-3m19.5 0a3 3 0 00-3-3H5.25a3 3 0 00-3 3m16.5 0h.008v.008h-.008v-.008zm-3 0h.008v.008h-.008v-.008z" />
                                       </svg>
                                       <span>Servers</span>
                                   </a>
                               </li>
                               <li>
                                   <a href="/sites"
                                       class="@if (request()->is('sites*')) sidebar-item-active @else sidebar-item @endif">
                                       <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2"
                                           stroke="currentColor" aria-hidden="true">
                                           <path stroke-linecap="round" stroke-linejoin="round"
                                               d="M12 21a9.004 9.004 0 008.716-6.747M12 21a9.004 9.004 0 01-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 017.843 4.582M12 3a8.997 8.997 0 00-7.843 4.582m15.686 0A11.953 11.953 0 0112 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0121 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0112 16.5c-3.162 0-6.133-.815-8.716-2.247m0 0A9.015 9.015 0 013 12c0-1.605.42-3.113 1.157-4.418" />
                                       </svg>
                                       <span>Sites</span>
                                   </a>
                               </li>
                               <li>
                                   <a href="/domains"
                                       class="@if (request()->is('domains*')) sidebar-item-active @else sidebar-item @endif">
                                       <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2"
                                           stroke="currentColor" aria-hidden="true">
                                           <path stroke-linecap="round" stroke-linejoin="round"
                                               d="M3.75 5.25h16.5m-16.5 4.5h16.5m-16.5 4.5h16.5m-16.5 4.5h16.5" />
                                       </svg>
                                       <span>Domains & DNS</span>
                                   </a>
                               </li>

                               {{-- Module Menu Items --}}
                               @php
                                   $menuManager = app(\App\Services\ModuleMenuManager::class);
                                   $moduleMenuItems = $menuManager->getMenuItems('main');
                               @endphp

                               @foreach ($moduleMenuItems as $menuItem)
                                   <li>
                                       <a href="{{ $menuItem->getUrl() }}"
                                           class="@if (request()->is(trim($menuItem->route, '/') . '*')) sidebar-item-active @else sidebar-item @endif"
                                           @if ($menuItem->is_external) target="{{ $menuItem->target }}" @endif>
                                           @if ($menuItem->icon)
                                               @if ($menuItem->icon_type === 'heroicon')
                                                   {!! $menuItem->icon !!}
                                               @elseif ($menuItem->icon_type === 'fontawesome')
                                                   <i class="{{ $menuItem->icon }} h-5 w-5 shrink-0"></i>
                                               @endif
                                           @else
                                               <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24"
                                                   stroke-width="2" stroke="currentColor">
                                                   <path stroke-linecap="round" stroke-linejoin="round"
                                                       d="M13.5 16.875h3.375m0 0h3.375m-3.375 0V13.5m0 3.375v3.375M6 10.5h2.25a2.25 2.25 0 002.25-2.25V6a2.25 2.25 0 00-2.25-2.25H6A2.25 2.25 0 003.75 6v2.25A2.25 2.25 0 006 10.5zm0 9.75h2.25A2.25 2.25 0 0010.5 18v-2.25a2.25 2.25 0 00-2.25-2.25H6a2.25 2.25 0 00-2.25 2.25V18A2.25 2.25 0 006 20.25zm9.75-9.75H18a2.25 2.25 0 002.25-2.25V6A2.25 2.25 0 0018 3.75h-2.25A2.25 2.25 0 0013.5 6v2.25a2.25 2.25 0 002.25 2.25z" />
                                               </svg>
                                           @endif
                                           <span>{{ $menuItem->title }}</span>
                                           @if ($menuItem->badge_type !== 'none' && $menuItem->getBadgeValue())
                                               <span
                                                   class="ml-auto flex h-6 w-6 shrink-0 items-center justify-center rounded-lg bg-{{ $menuItem->badge_color ?? 'blue' }}-600 text-[0.625rem] font-medium text-white">
                                                   {{ $menuItem->getBadgeValue() }}
                                               </span>
                                           @endif
                                       </a>
                                   </li>
                               @endforeach

                               {{-- Modules Management --}}
                               <li>
                                   <a href="/modules"
                                       class="@if (request()->is('modules*')) sidebar-item-active @else sidebar-item @endif">
                                       <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2"
                                           stroke="currentColor" aria-hidden="true">
                                           <path stroke-linecap="round" stroke-linejoin="round"
                                               d="M13.5 16.875h3.375m0 0h3.375m-3.375 0V13.5m0 3.375v3.375M6 10.5h2.25a2.25 2.25 0 002.25-2.25V6a2.25 2.25 0 00-2.25-2.25H6A2.25 2.25 0 003.75 6v2.25A2.25 2.25 0 006 10.5zm0 9.75h2.25A2.25 2.25 0 0010.5 18v-2.25a2.25 2.25 0 00-2.25-2.25H6a2.25 2.25 0 00-2.25 2.25V18A2.25 2.25 0 006 20.25zm9.75-9.75H18a2.25 2.25 0 002.25-2.25V6A2.25 2.25 0 0018 3.75h-2.25A2.25 2.25 0 0013.5 6v2.25a2.25 2.25 0 002.25 2.25z" />
                                       </svg>
                                       <span>Modules</span>
                                   </a>
                               </li>

                               {{-- Settings --}}
                               <li>
                                   <a href="/settings"
                                       class="@if (request()->is('settings*')) sidebar-item-active @else sidebar-item @endif">
                                       <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2"
                                           stroke="currentColor" aria-hidden="true">
                                           <path stroke-linecap="round" stroke-linejoin="round"
                                               d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281z" />
                                           <path stroke-linecap="round" stroke-linejoin="round"
                                               d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                       </svg>
                                       <span>Settings</span>
                                   </a>
                               </li>
                           </ul>
                       </li>

                       <li class="-mx-6 mt-auto border-t border-white/10">
                           <a href="/user/profile"
                               class="flex items-center gap-x-4 px-6 py-4 text-sm font-semibold leading-6 text-gray-300 hover:text-white hover:bg-white/5 transition-all duration-200">
                               <div
                                   class="h-10 w-10 rounded-full bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center text-white font-bold text-sm">
                                   {{ strtoupper(substr(Auth::user()->name, 0, 2)) }}
                               </div>
                               <div class="flex flex-col">
                                   <span class="text-white font-semibold">{{ Auth::user()->name }}</span>
                                   <span class="text-xs text-gray-400">View profile</span>
                               </div>
                           </a>
                       </li>
                   </ul>
               </nav>
           </div>
       </div>

<div>
    <div class="-mx-4 mt-10 ring-1 ring-gray-300 sm:mx-0 sm:rounded-lg">
        <table class="min-w-full divide-y divide-gray-300">
          <thead>
            <tr>
              <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6">Domain</th>
              <th scope="col" class="hidden px-3 py-3.5 text-left text-sm font-semibold text-gray-900 lg:table-cell">PHP</th>
              <th scope="col" class="relative text-right py-3.5 pl-3 pr-4 sm:pr-6">Actions</th>
            </tr>
          </thead>
          <tbody>
                @foreach ($sites as $site)
                <tr>
                    <td class="relative py-4 pl-4 pr-3 text-sm sm:pl-6 border-t border-gray-200">
                        <div class="font-medium text-gray-900">
                        {{ $site['domain'] }}
                        </div>
                    </td>
                    <td class="hidden px-3 py-3.5 text-sm text-gray-500 lg:table-cell border-t border-gray-200">{{ $site['php'] }}</td>
                    <td class="relative py-3.5 pl-3 pr-4 text-right text-sm font-medium sm:pr-6 border-t border-gray-200">
                        <a href="{{ route('site.edit', $site->site_id) }}" type="button" class="inline-flex items-center rounded-md bg-white px-2.5 py-1.5 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-30 disabled:hover:bg-white">Manage</a>
                        <a type="button" class="inline-flex items-center rounded-md bg-red-700 px-2.5 py-1.5 text-sm font-semibold text-white shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-30 disabled:hover:bg-red-900">Delete</a>
                    </td>
                </tr>
                @endforeach
          </tbody>
        </table>
      </div>
    </div>
</div>

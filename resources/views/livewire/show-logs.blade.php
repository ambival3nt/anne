<div>
    {{-- Care about people's approval and you will be their prisoner. --}}
    <table class="w-full p-6 text-md text-left">
        <thead>
        <tr class="dark:bg-orange-800">
            <th class="w-auto p-6 text-left">Timestamp</th>
            <th class="w-auto p-6">Level</th>
            <th class="w-auto p-6">Message</th>
        </tr>
        </thead>
        <tbody class="border-b dark:bg-gray-900 dark:border-gray-700">
    @foreach ($logData as $log)
        <tr class="border-green-50">
            <td class="p-4">
                {{ $log->logged_at }}
            </td>

            <td class="p-4">
                {{ $log->level }}
            </td>

            <td class="p-4">
                {{ $log->message }}
            </td>

        </tr>
    @endforeach
        </tbody>
    </table>

</div>

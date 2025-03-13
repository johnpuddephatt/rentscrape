<div x-data="{open: true}" @sidebar_open.window="open = true" @sidebar_close.window="open = false"  {{ $attributes->merge(['class' => 'w-72 border-t border-gray-200 fixed bottom-0 top-12 pt-24  right-0 border-l  overflow-x-auto dark:border-gray-700 border-gray-200 bg-gray-50 dark:bg-gray-950 py-8 p-4']) }}
    :class="{'hidden': !open, 'block': open}">
    {{ $getChildComponentContainer() }}
</div>

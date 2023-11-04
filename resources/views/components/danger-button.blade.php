<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center px-4 py-2 bg-rose-600 border border-rose-900 rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-rose-700 hover:shadow-glow hover:shadow-rose-900 active:bg-rose-900 focus:outline-none focus:ring-2 focus:ring-rose-600 focus:ring-offset-2 focus:ring-offset-midnight transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>

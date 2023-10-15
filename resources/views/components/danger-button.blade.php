<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center px-4 py-2 bg-rose-800 border border-rose-900/70 rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-rose-600 focus:outline-none focus-visible:ring-2 focus-visible:ring-rose-400 hover:transition-colors active:bg-rose-900 active:scale-95']) }}>

    {{ $slot }}
</button>

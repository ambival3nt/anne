<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center px-4 py-2 bg-ltblue-45 border border-ltblue-65 rounded-md font-semibold text-xs text-midnight-dk uppercase tracking-widest hover:bg-ltblue-65 focus-visible:bg-ltblue-65 focus:outline-none focus-visible:ring-2 focus-visible:ring-ltblue-75 focus-visible:ring-offset-1 focus-visible:ring-offset-black hover:shadow-glow hover:shadow-ltblue-75 hover:transition-colors active:transition-transform active:scale-95']) }}>

    {{ $slot }}
</button>

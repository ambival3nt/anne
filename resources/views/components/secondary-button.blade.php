<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center px-4 py-2 bg-ltblack border border-midnight-500 rounded-md font-semibold text-xs text-ltblue-55 uppercase tracking-widest hover:shadow-glow shadow-midnight-500 hover:bg-black focus:outline-none focus:ring-2 focus:ring-ltblue-55 focus:ring-offset-2 focus:ring-offset-midnight disabled:opacity-25 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>

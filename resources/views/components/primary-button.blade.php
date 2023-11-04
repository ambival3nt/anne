<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center px-4 py-2 bg-ltblue-55 border border-ltblue-55 rounded-md font-semibold text-xs text-black uppercase tracking-widest hover:bg-ltblue-75 focus:bg-ltblue-75 focus:outline-none focus:ring-2 focus:ring-ltblue-65 focus:ring-offset-2 focus:ring-offset-midnight disabled:opacity-25 transition ease-in-out duration-150']) }}>

    {{ $slot }}
</button>

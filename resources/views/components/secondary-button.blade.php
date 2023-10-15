<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center justify-center px-4 py-2 bg-ltblack border border-midnight-lt rounded-md font-semibold text-xs text-ltblue-45 uppercase tracking-widest hover:shadow-glow hover:text-ltblue-55 focus:text-ltblue55 focus:outline-none focus-visible:ring-2 focus-visible:ring-ltblue disabled:opacity-25  hover:ease-in-out hover:duration-200 ring ring-ltblue-55 ring-opacity-20 hover:transition-colors active:transition-transform active:scale-95']) }}>

    {{ $slot }}
</button>

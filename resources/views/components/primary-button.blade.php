<button {{ $attributes->merge(['type' => 'submit', 'class' => '
inline-flex 
items-center 
px-4 
py-2 
bg-ltblue 
border 
border-transparent 
rounded-md 
font-semibold 
text-xs 
text-black/70 
uppercase 
tracking-widest 
hover:bg-ltblue-55 
focus:bg-ltblue-55 
active:bg-ltblack
active:text-ltblue 
focus:outline-none 
transition 
ease-in-out 
duration-200']) }}>
    {{ $slot }}
</button>

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('v1.2') }}
        </h2>
    </x-slot>
    <main class="app-container">

        <!--main container-->
        <div class="main-container">

            <!-- container for nav-->
{{--            <nav>--}}
{{--                <div class="center"><h3>show me</h3></div>--}}
{{--                <!-- nav contents-->--}}
{{--                <div class="menu-bx">--}}
{{--                    <!-- sub-menu items -->--}}
{{--                    <!-- make this expand on click-->--}}
{{--                    <ul>--}}
{{--                        <li class="bx"><x-nav-link :href="route('botlogs')" :active="request()->routeIs('botlogs')">logs</x-nav-link></li>--}}
{{--                        <li class="bx"><a href="page2.html">users</a></li>--}}
{{--                        <li class="bx"><a href="page2.html">test</a></li>--}}
{{--                        <li class="bx"><a href="page2.html">think</a></li>--}}
{{--                    </ul>--}}
{{--                    <!-- links that don't populate the box-->--}}

{{--                </div>--}}
{{--                <!-- on click, slide out form; username submit then swap to passwpord submit-->--}}
{{--                <div class="bx"><h3><a href="page2.html">docs</a></h3></div>--}}
{{--                <div class="bx"><h3><a href=page2.html>furnace</a></h3></div>--}}
{{--                <div id="login-bx"><h3>sign in</h3></div>--}}
{{--                <div class="bx dight">&lt;</div>--}}
{{--            </nav>--}}
{{--            <!-- log box that populates-->--}}
            <div id="output-bx"> An aroma of malodorous fumes <br>
                The log box cannot refuse <br>
                A smell to make noses wrinkle and squeal <br>
                Farts in a log box, an olfactory ordeal.
                <br>a whole box of farts, like a box that will contain allll the farts. farts farts farts brrrrapffttt
                farts farts frrrrts farts farts farts fafffrrrrrrrprts farts farts ope farts </div>
            <div class="bx sneaky">fucks sake</div>
        </div>
    </main>

    <!-- FIX IT BITCH
    - change some divs to html elements
    - li menu open/close
    - gross fucking scrollbar
    - change style of output-bx for other formats?
    -->


    {{--    <div class="py-12">--}}
{{--        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">--}}
{{--            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">--}}
{{--                <div class="p-6 text-gray-900">--}}
{{--                    {{ __("You're logged in!") }}--}}
{{--                </div>--}}
{{--            </div>--}}
{{--            </div>--}}
{{--        </div>--}}
{{--    </div>--}}
</x-app-layout>

/*
    Classification Workbench
    Copyright (c) 2020-2021, WONCA ICPC-3 Foundation

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/

var isTouchDevice = 'ontouchstart' in document.documentElement;
var menuShown= false;
var menuDiv = '';

export function TouchMenu(divName, menuName)
{
    var mouseX = 0;
    var mouseDown = false;
    menuDiv = divName;

    $(menuName).click(function() {
        toggleMenu();
    });

    if (isTouchDevice) {
        $(divName).on('touchstart', function(){
            mouseDown = true;
            mouseX = event.changedTouches[0].pageX;
        });
    
        $(divName).on('touchmove', function(event) {
            if (mouseDown) {   
                var deltaX = event.changedTouches[0].pageX - mouseX;
                if (deltaX < 0) {
                    $(divName).css({left:deltaX});
                }
            }
        });
        
        $(divName).on('touchend', function(){
            mouseDown = false;
            if (event.changedTouches[0].pageX < 100) {
                closeMenu();
            }
            else  {
                $(divName).animate({left:'0px'});
            }
        });
    }
    else { // desktop, thus mouse
        $(divName).mousemove(function(event) {
            var deltaX = event.pageX - mouseX;
            if (deltaX > 180) {
                closeMenu();
            }
            else if ((deltaX < 10) && (!menuShown)) {
                toggleMenu();
            }
        });
    }
}


function toggleMenu()
{
    if (menuShown) {
        closeMenu();
    }
    else {
        $(menuDiv).animate({left:'0px'});
        menuShown = true;
    }
}

export function closeMenu()
{
    if (menuShown) {
        $(menuDiv).animate({left:'-210px'});
        menuShown = false;
    }
}

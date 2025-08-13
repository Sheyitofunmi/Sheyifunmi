// Quick explanation
// sP =  Scroll Position
// stPrev = Scroll Top Previous

var fixed = {

    config: {
        header: document.querySelector('.header'),
        scrolled: null,
        stPrev: 0,
        delta: 5,
        get height() {
            return this.header.offsetHeight;
        }
    },

    init: function(){

        setInterval(fixed.scrolledCheck, 250);

        window.addEventListener('scroll', function(){
                fixed.config.scrolled = true;
        });

    },

    scrolledCheck: function(){
        if (fixed.config.scrolled) {

            fixed.hasScrolled();
            fixed.config.scrolled = false;

        }
    },

    hasScrolled: function(){

        var sP = window.pageYOffset;

        if(Math.abs(fixed.config.stPrev - sP) <= fixed.config.delta)
            return;

        if (sP > fixed.config.stPrev && sP > fixed.config.height){
                // Scroll Down

                fixed.config.header.classList.add('nav-up')
                fixed.config.header.classList.remove('nav-down');
                document.body.classList.add('nav-up');
				document.body.classList.remove('nav-down');
				document.body.classList.remove('nav-top');


        } else{

            if(sP + window.innerHeight < document.documentElement.scrollHeight){


                fixed.config.header.classList.add('nav-down');
                fixed.config.header.classList.remove('nav-up');
				document.body.classList.add('nav-down');
                document.body.classList.remove('nav-up');
				document.body.classList.remove('nav-top');


            }

			if(window.scrollY==0){

				document.body.classList.add('nav-top');
				document.body.classList.remove('nav-up');
				document.body.classList.remove('nav-down');


			}

        }

        fixed.config.stPrev = sP;
    },

}

fixed.init();

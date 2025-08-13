const donate = {

    // get all donation sections, and then set two empty vars for the tabs and panels, so we have somewhere to stash them during the loop
    els:{
        donationsCont: document.querySelectorAll('.donations'),
    },

    init(){
        // set up each donations component
        this.els.donationsCont.forEach(container => donate.setupContainer(container));
    },

    setupContainer(container){

        // get stuff to use later
        const tabs = container.querySelectorAll('.frequency-tab');
        const panels = container.querySelectorAll('.frequency-tabpanel');
        const button = container.querySelector('.donations_button');
        const custom_amount = container.querySelectorAll('.custom_amount');

        // find all tabs, if more than one set up tab stuff
        if(tabs.length > 1){
            donate.setupTabs(tabs, panels);
        }

        // set up amount tabs
        panels.forEach((panel,index) => {

            const amountTabs = panel.querySelectorAll('.amount-tab');
            const amountPanels = panel.querySelectorAll('.amount-tabpanel');

            // set up tab event to swap panels
            donate.setupTabs(amountTabs, amountPanels);

            // set up accessibility keystroke event
            donate.setUpKeystroke(amountTabs, amountPanels);

            // setup active state on tabs by setting first tab and panel as visible and selected
            if(index != 0){
                donate.setInitialTabs(amountTabs, amountPanels, false);
            }else{
                donate.setInitialTabs(amountTabs, amountPanels);
            }

        });

        // setup active state on tabs by setting first tab and panel as visible and selected
        donate.setInitialTabs(tabs, panels, false);

        // setup custom input handler
        button.addEventListener('click', donate.donationButtonHandler);

        // setup button click event
        for (let i = 0; i < custom_amount.length; i++) {
            custom_amount[i].addEventListener('input', function() {
                donate.customAmountHandler(custom_amount[i], button)
            });
        }

    },

    setupTabs(tabs, panels){

        tabs.forEach(tab => tab.addEventListener('click', function(event){
            donate.handleTabClick(event,tabs, panels, tab);
        }));

    },

    setUpKeystroke(tabs, panels){

        tabs.forEach(tab => tab.addEventListener('keyup', function(event){
            // if not an arrow, return false
            if(!event.key.includes('Arrow')){
                return false;
            }

            // set the active element as checked
            document.activeElement.checked = true;

        }));

    },

    handleTabClick(event, tabs, panels, tab){
        // prevent default
        event.preventDefault();

        // hide all tab panels
        panels.forEach(panel => {
            panel.hidden = true;
        });

        // mark all tabs as unselected
        tabs.forEach(tab => {
            tab.setAttribute('aria-selected', false);
        });

        // mark the clicked tab as selected
        event.currentTarget.setAttribute('aria-selected', true);

        // find the associated tabPanel and show it!
        const { id } = event.currentTarget;

        const tabPanel = Array.from(panels).find(
            panel => panel.getAttribute('data-tabpanel') === id
        );

        tabPanel.hidden = false;

        // tab specific actions
        if(tab.classList.contains('frequency-tab')){

            // set correct option as first
            donate.setInitialTabs(tabPanel.querySelectorAll('.amount-tab'),tabPanel.querySelectorAll('.amount-tabpanel'), true, true);

        }else{

            donate.amountHandler(tab);
            event.currentTarget.querySelector('input').focus();

        }

    },

    amountHandler(item){
        // find radio button and select it
        const button = item.closest('form').querySelector('.donations_button');
        let radioButton = item.querySelector('input[name=amount]');

        // set correct radio button to checked
        radioButton.checked = true;

        // set custom amount
        document.querySelectorAll('.custom_amount').value = radioButton.value;

        // amend action data on button
        button.setAttribute('data-frequency_string', item.dataset.string);
        button.setAttribute('data-donation', radioButton.value);

    },



    setInitialTabs(tabs, panels, setOption = true, clearCustom = false){

        // hide all tab panels first
        panels.forEach(panel => {
            panel.hidden = true;
        });

        tabs[0].setAttribute('aria-selected', true);
        panels[0].hidden = false;

        // set option box to correct option if set option is set to true
        if(setOption){
            donate.amountHandler(tabs[0]);
            var activeAmount = tabs[0].querySelector('input').value;
            document.querySelectorAll('.custom_amount').value = activeAmount;
        }

    },

    customAmountHandler(custom_amount, button){
        button.setAttribute('data-donation', custom_amount.value);
    },

    donationButtonHandler(event){
        event.preventDefault();

        // grab current target and stash it, then grab form to set action and submit
        const _this = event.currentTarget;
        const form = _this.closest('form');
        form.action = $('#donate').attr('data-action');
		var splArr = form.action.split('?');

        var donateAmount = $('.donations_button').attr('data-donation');

        if (_this.getAttribute('data-multiplier')) {
            donateAmount = (donateAmount * _this.getAttribute('data-multiplier'));
        }

        if (splArr[1]) {
            form.action = `${ splArr[0] + '?' + $('.donations_button').attr('data-frequency_string') +'&amount=' + donateAmount}`;
        } else {
            form.action = `${ form.action + '?' + $('.donations_button').attr('data-frequency_string') +'&amount=' + donateAmount}`;
        }

        // submit form
        setTimeout(function() {
            window.location.href = form.action;
        }, 250)
    }
}

if(donate.els.donationsCont.length){
    donate.init();
}

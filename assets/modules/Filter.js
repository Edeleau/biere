import {Flipper, spring} from 'flip-toolkit';

/**
 * @property {HTMLElement} pagination
 * @property {HTMLElement} content
 * @property {HTMLFormElement} form
 */

export default class Filter {

    /**
     * @param {HTMLElement|null}element
     */
    constructor(element) {
        if (element === null) {
            return;
        }
        this.pagination = element.querySelector('.js-filter-pagination');
        this.content = element.querySelector('.js-filter-content');
        this.form = element.querySelector('.js-filter-form');
        this.bindEvents();
    }
    /**
     * Ajoute les comportements aux différents elements
     */
    bindEvents() {
        this.form.querySelectorAll('input[type=text]').forEach(input => {
            input.addEventListener('keyup', this.loadForm.bind(this));
        })
        this.pagination.addEventListener('click', e=>{
            if(e.target.tagName === 'A'){
                e.preventDefault();
                this.loadurl(e.target.getAttribute('href'));
            }
        })
        this.form.querySelectorAll('input[type=text]').forEach(input => {
            input.addEventListener('change', this.loadForm.bind(this));
        })

        this.form.querySelectorAll('select').forEach(select => {
            select.addEventListener('change', this.loadForm.bind(this));
        })
    }
    async loadForm() {
        const data = new FormData(this.form);
        const url = new URL(this.form.getAttribute('action') || window.location.href);
        const params = new URLSearchParams();
        data.forEach((value, key) => {
            params.append(key, value);
        })

        return this.loadurl(url.pathname + '?' + params.toString());
    }
    async loadurl(url) {
        // const pour éviter de tomber sur une page en json qui va être mis en cache par les navigateurs
        const ajaxUrl = url + '&ajax=1';
        const response = await fetch(ajaxUrl, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        if (response.status >= 200 && response.status < 300) {
            const data = await response.json();
            this.flipContent(data.content)
            this.pagination.innerHTML = data.pagination;
            history.replaceState({}, '', url);
        }
        else {
            console.error(response);
        }
    }

    /**
     * Remplace les élément de la grille avec une effet d'animation flip
     * @param {string} content 
     */
    flipContent(content){
        const springConfig = "veryGentle";
        const exitSpring = function (element, index, onComplete){
            spring({
                config: 'stiff',
                values: {
                  translateY: [0, -20],
                  opacity: [1, 0]
                },
                onUpdate: ({ translateY, opacity }) => {
                    element.style.opacity = opacity;
                    element.style.transform = `translateY(${translateY}px)`;
                },
                onComplete
              });
        }
        const appearSpring = function (element, index){
            spring({
                config: 'stiff',
                values: {
                  translateY: [20, 0],
                  opacity: [0, 1]
                },
                onUpdate: ({ translateY, opacity }) => {
                    element.style.opacity = opacity;
                    element.style.transform = `translateY(${translateY}px)`;
                },
                delay: index *2,
              });
        }
        const flipper = new Flipper({
            element: this.content
        });
        this.content.children.forEach(element => {
            flipper.addFlipped({
                element,
                spring: springConfig,
                flipId: element.id,
                shouldFlip: false,
                onExit: exitSpring
            });
        });
        flipper.recordBeforeUpdate();
        this.content.innerHTML = content;
        this.content.children.forEach(element => {
            flipper.addFlipped({
                element,
                spring: springConfig,
                flipId: element.id,
                onAppear: appearSpring
            });
        });
        flipper.update();
    }
    

}
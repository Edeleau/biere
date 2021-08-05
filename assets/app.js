/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (app.css in this case)
// start the Stimulus application
import './bootstrap';
import 'bootstrap'
import './styles/app.scss';
import './header';
import './footer';
import './noUiSlider';
import Filter from './modules/Filter.js'

new Filter(document.querySelector('.js-filter'))
// You can specify which plugins you need
import { Tooltip, Toast, Popover } from 'bootstrap';


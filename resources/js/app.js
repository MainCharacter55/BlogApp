import './bootstrap';
import { initPostFeedInteractions } from './modules/post-feed';
import { initPostShowInteractions } from './modules/post-show';

document.addEventListener('DOMContentLoaded', () => {
	initPostFeedInteractions(document);
	initPostShowInteractions(document);
});

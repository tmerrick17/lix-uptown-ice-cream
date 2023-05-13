// Shrinking header on scroll down
// document.addEventListener('scroll', () => {
//   if (document.documentElement.scrollTop > 100) {
//     document.querySelector('header').classList.add('header--shrink');
//     document.querySelector('.logo').classList.add('logo--shrink');
//   } else {
//     document.querySelector('header').classList.remove('header--shrink');
//     document.querySelector('.logo').classList.remove('logo--shrink');
//   }
// });

import { throttle } from 'lodash';

const handleScroll = throttle(() => {
  if (document.documentElement.scrollTop > 100) {
    document.querySelector('header').classList.add('header--shrink');
    document.querySelector('.logo').classList.add('logo--shrink');
  } else {
    document.querySelector('header').classList.remove('header--shrink');
    document.querySelector('.logo').classList.remove('logo--shrink');
  }
}, 400); // Set the throttle time to x milliseconds

document.addEventListener('scroll', handleScroll);

// Update menu items on smaller screen size
const navItemNewFlaves = document.querySelector('.menu-item--new-flaves a');
const navItemOrigFlaves = document.querySelector('.menu-item--orig-flaves a');
const navItemGiftCards = document.querySelector('.menu-item--gift-card a');

const emojiIceCream = '🍦';
const emojiGift = '🎁';

const addEmojiToNavItems = () => {
  console.log(navItemNewFlaves);
  if (window.innerWidth < 500) {
    navItemNewFlaves.innerText = `New${emojiIceCream}`;
    navItemOrigFlaves.innerText = `Orig${emojiIceCream}`;
    navItemGiftCards.innerText = `${emojiGift} Cards`;
  } else {
    navItemNewFlaves.innerText = 'New Flaves';
    navItemOrigFlaves.innerText = 'Orig Flaves';
    navItemGiftCards.innerText = 'Gift Cards';
  }
}

addEmojiToNavItems();

window.addEventListener('resize', () => {
  addEmojiToNavItems();
});

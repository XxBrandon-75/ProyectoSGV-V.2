const right = document.getElementById('right');
const registerBtn = document.getElementById('register-btn');

registerBtn.addEventListener('click', () => {
    right.classList.toggle('active');
    registerBtn.textContent = right.classList.contains('active') 
    ? 'Acceso con cuenta' 
    : 'Unete al equipo'
});


(function imageRotator(){
    const imgEl = document.querySelector('.left img');
    if(!imgEl) return; 

    const images = [
        'img/principal.jpg',
        'img/segunda.jpg',
        'img/tercera.jpeg'
    ];

    const preloaded = images.map(src => { const i = new Image(); i.src = src; return i; });

    let index = 0;
    const intervalMs = 5000; 

 
    imgEl.src = images[index];

    setInterval(()=>{
        // next index
        index = (index + 1) % images.length;

        // fade out -> change src -> fade in
        imgEl.classList.add('fade-out');
        setTimeout(()=>{
            imgEl.src = images[index];
            imgEl.classList.remove('fade-out');
            imgEl.classList.add('fade-in');
            setTimeout(()=> imgEl.classList.remove('fade-in'), 600);
        }, 400); // small delay to allow fade-out

    }, intervalMs);
})();
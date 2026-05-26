document.addEventListener('DOMContentLoaded', () => {
    const sidebar = document.getElementById('sidebar');
    const collapseBtn = document.getElementById('collapse-btn');
    const hamburgerBtn = document.getElementById('hamburger-btn');
    const themeBtn = document.getElementById('theme-toggle');
    const avatarDropdown = document.getElementById('avatar-dropdown');
    const notificationCenter = document.getElementById('notification-center');
    const ncClose = document.getElementById('nc-close');

    /* -------------------------------
       Sidebar collapse / dock hover
    --------------------------------*/
    collapseBtn?.addEventListener('click', () => {
        sidebar.classList.toggle('collapsed');
        document.querySelector('.glass-main-wrapper').style.marginLeft =
            sidebar.classList.contains('collapsed') ? '60px' : '220px';
    });

    hamburgerBtn?.addEventListener('click', () => {
        const isHidden = sidebar.style.transform === 'translateX(-120%)';
        sidebar.style.transform = isHidden ? 'translateX(0)' : 'translateX(-120%)';
    });

    // Auto-expand if active route inside sidebar
    if(sidebar.querySelector('.menu-item.active, .submenu.open')){
        sidebar.classList.add('hover');
        sidebar.querySelectorAll('.menu-item.parent').forEach(parent => {
            if(parent.querySelector('.submenu-item.active')){
                parent.querySelector('.submenu')?.classList.add('open');
            }
        });
    }

    /* -------------------------------
       Submenu toggle
    --------------------------------*/
    document.querySelectorAll('.menu-item.parent .parent-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const parent = btn.parentElement;
            const submenu = parent.querySelector('.submenu');
            if(submenu.classList.contains('open')){
                submenu.classList.remove('open');
            } else {
                document.querySelectorAll('.submenu.open').forEach(s => s.classList.remove('open'));
                submenu.classList.add('open');
            }
        });
    });

    /* -------------------------------
       Avatar dropdown
    --------------------------------*/
    avatarDropdown?.querySelector('.avatar-btn')?.addEventListener('click', e => {
        e.stopPropagation();
        avatarDropdown.classList.toggle('active');
    });
    document.addEventListener('click', () => avatarDropdown?.classList.remove('active'));

    /* -------------------------------
       Theme toggle
    --------------------------------*/
    themeBtn?.addEventListener('click', () => {
        if(document.body.classList.contains('dark')){
            document.body.classList.replace('dark','light');
            localStorage.setItem('theme','light');
        } else {
            document.body.classList.replace('light','dark');
            localStorage.setItem('theme','dark');
        }
    });
    const savedTheme = localStorage.getItem('theme');
    if(savedTheme) document.body.classList.add(savedTheme);
    else document.body.classList.add('dark');

    /* -------------------------------
       Notification center
    --------------------------------*/
    themeBtn?.addEventListener('dblclick', () => {
        if(notificationCenter){
            notificationCenter.style.display = notificationCenter.style.display==='flex' ? 'none' : 'flex';
        }
    });
    ncClose?.addEventListener('click', () => notificationCenter.style.display='none');

    /* -------------------------------
       Draggable panels
    --------------------------------*/
    document.querySelectorAll('.window-panel').forEach(panel => {
        let isDragging=false, offsetX=0, offsetY=0;
        const header = panel.querySelector('.panel-header');
        header?.addEventListener('mousedown', e => {
            isDragging=true;
            offsetX = e.clientX-panel.offsetLeft;
            offsetY = e.clientY-panel.offsetTop;
            panel.style.cursor='grabbing';
        });
        document.addEventListener('mousemove', e => {
            if(isDragging){
                panel.style.left = (e.clientX-offsetX)+'px';
                panel.style.top  = (e.clientY-offsetY)+'px';
            }
        });
        document.addEventListener('mouseup', () => {
            isDragging=false;
            panel.style.cursor='grab';
        });
    });

    /* -------------------------------
       Mobile swipe gesture
    --------------------------------*/
    let startX = 0;
    document.addEventListener('touchstart', e => startX = e.touches[0].clientX);
    document.addEventListener('touchend', e => {
        let endX = e.changedTouches[0].clientX;
        if(startX < 50 && endX > 120) sidebar.style.transform='translateX(0)'; // swipe right = open
        if(startX > 200 && endX < 100) sidebar.style.transform='translateX(-120%)'; // swipe left = close
    });
});
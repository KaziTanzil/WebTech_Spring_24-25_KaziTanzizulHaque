const tan = document.getElementById("lol");
{
  tan.addEventListener("click", doSomething);
}

function doSomething() {
  alert("Music Player is not installed");
}

const logo2 = document.getElementById("logo2");


if (logo2) {
  logo2.addEventListener("mouseover", () => { alert("Searchber") });
}

const topRight = document.getElementById("topRight");


if(topRight)
  {
    const links=topRight.querySelectorAll("a");
    links.forEach(link=>{
      link.addEventListener("mouseover",()=> link.style.textDecoration="underline");
      link.addEventListener("mouseleave",()=>link.style.textDecoration="none");
    })
  }
  



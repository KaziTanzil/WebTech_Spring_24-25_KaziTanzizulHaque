
const submit=document.getElementById("submitbtn")

if(submit){ submit.addEventListener("click",()=>

    {
        const username=document.getElementById("username")
        
            const usernameInput=username.value;
            const password=document.getElementById("password").value
            const msg = document.getElementById("msg")
            const p1 =document.getElementById("p1")
            const p2=document.getElementById("p2")
            const p3=document.getElementById("p3") 


            // Reset all messages first
            msg.style.display = "none";
            p1.style.display = "none";
            p2.style.display = "none";
            p3.style.display = "none";

            
            if (usernameInput != "" && /^[a-zA-Z]/.test(usernameInput)) {

              if (password != "" && password.length >= 8) {
                
                if (password.length >= 8 && /[!@#$%^&*(),.?":{}|<>]/.test(password)) {
                  
                  msg.style.display = "none"; 
                  p1.style.display = "none";  
                  p2.style.display = "none";
                  p3.style.display = "none";
                } else {
                  
                  msg.style.display = "block";
                  p3.style.display = "block";
                }
              } else {
               
                msg.style.display = "block";
                p2.style.display = "block";
              }
            } else {
              
              msg.style.display = "block";
              p1.style.display = "block";
            }
            
                

               
        })
    }

    const okbtn=document.getElementById("okbtn")
    if(okbtn)
      {
            okbtn.addEventListener("click",()=>{msg.style.display="None";})

          }  



  console.log(2+'2')

         
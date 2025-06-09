const locations = (variant, where) =>{
    let query = ""
    let selected_code

    // can do two parameters instead of creating another js and php file for 3 selected fields with regions etc.
    if(variant === 'region'){

        if(where == "sdn-region"){
            selected_code = document.getElementById('sdn-region-select').value
        }else if(where == "pa-region"){
            selected_code = document.getElementById('hperson-region-select-pa').value
        }else if(where == "ca-region"){
            selected_code = document.getElementById('hperson-region-select-ca').value
        }else if(where == "cwa-region"){
            selected_code = document.getElementById('hperson-region-select-cwa').value
        } else if(where == "ofw-region"){
            selected_code = document.getElementById('hperson-region-select-ofw').value
        }

        query = "region_code"
    }else if(variant === 'province'){
        if(where == "sdn-province"){
            selected_code = document.getElementById('sdn-province-select').value
        }else if(where == "pa-province"){
            selected_code = document.getElementById('hperson-province-select-pa').value
        }else if(where == "ca-province"){
            selected_code = document.getElementById('hperson-province-select-ca').value
        }else if(where == "cwa-province"){
            selected_code = document.getElementById('hperson-province-select-cwa').value
        } else if(where == "ofw-province"){
            selected_code = document.getElementById('hperson-province-select-ofw').value
        }

        query = "province_code"

    }else if(variant === 'city'){
        if(where == "sdn-city"){
            selected_code = document.getElementById('sdn-city-select').value
        }else if(where == "pa-city"){
            selected_code = document.getElementById('hperson-city-select-pa').value
        }else if(where == "ca-city"){
            selected_code = document.getElementById('hperson-city-select-ca').value
        }else if(where == "cwa-city"){
            selected_code = document.getElementById('hperson-city-select-cwa').value
        }else if(where == "ofw-city"){
            selected_code = document.getElementById('hperson-city-select-ofw').value
        }

        query = "city_code"
    }

    // fetch("php/get_locations.php?" + query + "=" + selected_code + "&" + "val=" + variant)
    fetch("../SDN/get_locations.php?" + query + "=" + selected_code + "&" + "val=" + variant)
    .then(response => response.text())
    .then(data =>{
        if(variant === 'region'){
           
            if(where == "sdn-region"){
                document.getElementById('sdn-province-select').innerHTML = data;
            } else if(where == "pa-region"){
                document.getElementById('hperson-province-select-pa').innerHTML = data;
            } else if(where == "ca-region"){
                document.getElementById('hperson-province-select-ca').innerHTML = data;
            } else if(where == "cwa-region"){
                document.getElementById('hperson-province-select-cwa').innerHTML = data;
            } else if(where == "ofw-region"){
                document.getElementById('hperson-province-select-ofw').innerHTML = data;
            }

        }else if(variant === 'province'){
            // document.getElementById('sdn-city-select').innerHTML = data;
            if(where == "sdn-province"){
                document.getElementById('sdn-city-select').innerHTML = data;
            } else if(where == "pa-province"){
                document.getElementById('hperson-city-select-pa').innerHTML = data;
            } else if(where == "ca-province"){
                document.getElementById('hperson-city-select-ca').innerHTML = data;
            }   else if(where == "cwa-province"){
                document.getElementById('hperson-city-select-cwa').innerHTML = data;
            } else if(where == "ofw-province"){
                document.getElementById('hperson-city-select-ofw').innerHTML = data;
            }

        }else if(variant === 'city'){
            // splice the last 4 character of the data to get the zip code
            const zip_code = data.slice(-4);
            // document.getElementById('sdn-brgy-select').innerHTML = data;
            // document.getElementById('sdn-zip-code').value = zip_code

            if(where == "sdn-city"){
                document.getElementById('sdn-brgy-select').innerHTML = data;
                document.getElementById('sdn-zip-code').textContent = zip_code
            } else if(where == "pa-city"){
                document.getElementById('hperson-brgy-select-pa').innerHTML = data;
            } else if(where == "ca-city"){
                document.getElementById('hperson-brgy-select-ca').innerHTML = data;
            } else if(where == "cwa-city"){
                document.getElementById('hperson-brgy-select-cwa').innerHTML = data;
            } 
            // else if(where == "ofw-city"){
            //     document.getElementById('hperson-brgy-select-ofw').innerHTML = data;
            // }
        }
    })
}

function getLocations(variant, where){
    locations(variant, where)
}   
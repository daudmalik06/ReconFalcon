## Recon Falcon


=========================================  


## Introduction
A small Php application to fetch archive url snapshots from archive.org.  
using it you can fetch complete list of snapshot urls of any year or 
complete list of all years possible.  
Made Specially for penetration testing purpose.  
**This application is powered by [WMB-Scrapper](https://github.com/daudmalik06/WMB-Scrapper)**



## Installation

Clone this repository,

```
    git clone https://github.com/daudmalik06/ReconFalcon
    cd ReconFalcon
    php recon
```

## Requirements

- This application requires php 7+  
- multi threading is available as optional, if you have php [pthreads](https://github.com/krakjoe/pthreads) installed you can use that
to speed up the process.


## Information

- it saves all results in Output directory, e.g if output-name=facebook it will make a directory called  
facebook.com in Output directory and will save results in that directory.
- single url snapshot is processed in a single thread 
 
## Usage
 
For Usage please check help command


```
php recon --help
```

## Screenshots

![ReconFalcon Help](/screenShots/reconFalconHelp.JPG)
![ReconFalcon Example](/screenShots/reconFalconProcessing.JPG)

## License
The **Recon Falcon** is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

## Contribution
Thanks to all of the contributors ,  

## Author
Dawood Ikhlaq and Open source community
    

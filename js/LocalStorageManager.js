class LocalStorageManager {
  isDebug;
  constructor(isDebug=false){
    this.isDebug = isDebug;
  }

  set(key, value,isArray=false){
    let input = "";
    if(isArray){
      input = JSON.stringify(value);
    } else {
      input = value;
    }
    localStorage.setItem(key,input);
    if(this.isDebug){
      console.log(this.constructor.name + ' > setlocalStrage() key:'+ key + ' value:' + input);
    }
    
  }

  getByKeyName(key, isArray=false){
    const output = localStorage.getItem(key);
    let array =[];
    if(this.isDebug){
      console.log(this.constructor.name + ' > getByKeyName() key:'+ key + ' value:' + output);
    }
    if(isArray){
      return JSON.parse(output);
      // console.log('getByKeyName '+ array[0] + array[1]);
      // return array;
    }
    return output;
  }

  getByIndex(num, isNum=false){
    const key = localStorage.key(num);
    if(this.isDebug){
      console.log(this.constructor.name + ' > getlocalStrage() index:'+ num + ' key:' + key);
    }
    if(isNum){
      return Number(result);
    }
    return key;
  }

  deleteItem(key){
    if(this.isDebug){
      console.log(this.constructor.name + ' > deleteLocalStrage() key:'+ key + ' deleted!');
    }
    localStorage.removeItem(key);
  
  }

  allClear(){
    if(this.isDebug){
      console.log(this.constructor.name + ' > allClear() key:'+ key + ' deleted!');
    }
    localStorage.clear();
  }

}
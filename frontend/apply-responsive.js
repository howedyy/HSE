import fs from 'fs';
import path from 'path';

const walkSync = (dir, filelist = []) => {
  fs.readdirSync(dir).forEach(file => {
    const dirFile = path.join(dir, file);
    try {
      filelist = walkSync(dirFile, filelist);
    } catch (err) {
      if (err.code === 'ENOTDIR' || err.code === 'EBADF') {
        if (dirFile.endsWith('.tsx') || dirFile.endsWith('.ts')) {
          filelist.push(dirFile);
        }
      } else {
        throw err;
      }
    }
  });
  return filelist;
};

const applyResponsiveClasses = () => {
  const files = walkSync(path.join(process.cwd(), 'src/presentation/pages'));
  
  files.forEach(file => {
    let content = fs.readFileSync(file, 'utf8');
    let newContent = content;
    
    // Modals
    newContent = newContent.replace(/w-full max-w-3xl my-8/g, 'w-[95%] md:w-full max-w-3xl my-4 md:my-8');
    newContent = newContent.replace(/w-full max-w-2xl my-8/g, 'w-[95%] md:w-full max-w-2xl my-4 md:my-8');
    newContent = newContent.replace(/w-full max-w-4xl my-8/g, 'w-[95%] md:w-full max-w-4xl my-4 md:my-8');
    newContent = newContent.replace(/w-full max-w-md my-8/g, 'w-[95%] md:w-full max-w-md my-4 md:my-8');
    
    // Padding
    newContent = newContent.replace(/p-8 space-y-6/g, 'p-4 md:p-8 space-y-4 md:space-y-6');
    newContent = newContent.replace(/p-8 space-y-8/g, 'p-4 md:p-8 space-y-6 md:space-y-8');
    
    // Table overflow
    newContent = newContent.replace(/className="overflow-x-auto"/g, 'className="overflow-x-auto w-full"');
    
    // Page container
    newContent = newContent.replace(/max-w-7xl mx-auto space-y-8/g, 'max-w-7xl mx-auto space-y-4 md:space-y-8');
    
    // PTW specific responsive grids
    newContent = newContent.replace(/grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-7 gap-6/g, 'grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-7 gap-4 md:gap-6');

    if (newContent !== content) {
      fs.writeFileSync(file, newContent, 'utf8');
      console.log(`Updated ${file}`);
    }
  });
};

applyResponsiveClasses();

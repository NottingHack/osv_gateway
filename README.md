Open Vend System Web Gateway

The server side part of the Open Vend System has two parts:

Web Socket Server
	Listens for incoming requests from OVS vending machines.
	Can handle multiple machines - each machine announces its machine ID
	Socket is kept open
	Sends information requests down the socket before a vend starts
	Sends vend instructions and waits for confirmation.

Gateway
	Each product has an unique URL, with associated QR code.
	(URLs are unique by product and machine ID)
	Product page provides info on product and link to pay (paypal?)
	Maintains stock level and checks before vend
	When paid, large "VEND" button is displayed, which triggers vend instruction
	Admin can add products, manage machines, etc
